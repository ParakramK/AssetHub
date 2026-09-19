<?php

use App\Models\AuditLog;
use App\Models\Company;
use App\Models\ServerCredential;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

beforeEach(fn () => app(PermissionRegistrar::class)->forgetCachedPermissions());

test('logs model creation with the acting user', function () {
    $user = superAdminUser();

    $this->actingAs($user)->post('/companies', ['name' => 'Acme Corporation']);

    $company = Company::where('name', 'Acme Corporation')->firstOrFail();
    $log = AuditLog::query()
        ->where('auditable_type', Company::class)
        ->where('auditable_id', $company->id)
        ->where('action', 'created')
        ->firstOrFail();

    expect($log->user_id)->toBe($user->id)
        ->and($log->new_values['name'])->toBe('Acme Corporation')
        ->and($log->old_values)->toBeNull();
});

test('logs model updates with only the changed attributes', function () {
    $company = Company::factory()->create(['name' => 'Old Name']);

    $company->update(['name' => 'New Name']);

    $log = AuditLog::query()
        ->where('auditable_type', Company::class)
        ->where('auditable_id', $company->id)
        ->where('action', 'updated')
        ->firstOrFail();

    expect($log->old_values)->toBe(['name' => 'Old Name'])
        ->and($log->new_values)->toBe(['name' => 'New Name']);
});

test('logs model deletion with the previous attributes', function () {
    $company = Company::factory()->create(['name' => 'Doomed Corp']);

    $company->delete();

    $log = AuditLog::query()
        ->where('auditable_type', Company::class)
        ->where('auditable_id', $company->id)
        ->where('action', 'deleted')
        ->firstOrFail();

    expect($log->old_values['name'])->toBe('Doomed Corp')
        ->and($log->new_values)->toBeNull();
});

test('never stores secrets in audit entries', function () {
    $credential = ServerCredential::factory()->create(['password' => 'super-secret']);

    $logs = AuditLog::query()
        ->where('auditable_type', ServerCredential::class)
        ->where('auditable_id', $credential->id)
        ->get();

    expect($logs)->not->toBeEmpty();

    foreach ($logs as $log) {
        expect(json_encode([$log->old_values, $log->new_values]))->not->toContain('super-secret');
    }
});

test('logs credential reveals without storing the secret', function () {
    $credential = ServerCredential::factory()->create(['password' => 'super-secret']);

    $this->actingAs(superAdminUser())
        ->get("/servers/{$credential->server_id}/credentials/{$credential->id}/reveal")
        ->assertOk();

    $log = AuditLog::query()
        ->where('auditable_type', ServerCredential::class)
        ->where('auditable_id', $credential->id)
        ->where('action', 'revealed')
        ->firstOrFail();

    expect(json_encode([$log->old_values, $log->new_values]))->not->toContain('super-secret')
        ->and($log->new_values['username'])->toBe($credential->username);
});

test('redirects guests to the login page', function () {
    $log = AuditLog::factory()->create();

    $this->get('/audit-logs')->assertRedirect('/login');
    $this->delete("/audit-logs/{$log->id}")->assertRedirect('/login');
    $this->post('/audit-logs/prune', ['days' => 90])->assertRedirect('/login');
});

test('returns 403 for non-super-admins', function () {
    $log = AuditLog::factory()->create();

    $this->actingAs(standardUser())->get('/audit-logs')->assertForbidden();
    $this->actingAs(standardUser())->delete("/audit-logs/{$log->id}")->assertForbidden();
    $this->actingAs(standardUser())->post('/audit-logs/prune', ['days' => 90])->assertForbidden();
});

test('renders the audit log list for super admins', function () {
    Company::factory()->create(['name' => 'Acme Corporation']);

    $this->actingAs(superAdminUser())->get('/audit-logs')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('audit-logs/index')
            ->has('logs')
            ->has('filters')
            ->has('actions')
        );
});

test('deletes a single entry and prunes old entries', function () {
    $recent = AuditLog::factory()->create();
    $old = AuditLog::factory()->create(['created_at' => now()->subDays(100)]);

    $this->actingAs(superAdminUser())->delete("/audit-logs/{$recent->id}")
        ->assertRedirect('/audit-logs');

    expect(AuditLog::where('id', $recent->id)->exists())->toBeFalse();

    $this->actingAs(superAdminUser())->post('/audit-logs/prune', ['days' => 90])
        ->assertRedirect('/audit-logs');

    expect(AuditLog::where('id', $old->id)->exists())->toBeFalse();
});

test('prune command deletes entries beyond the retention window', function () {
    $old = AuditLog::factory()->create(['created_at' => now()->subDays(100)]);
    $recent = AuditLog::factory()->create();

    $this->artisan('audit-logs:prune', ['--days' => 90])->assertSuccessful();

    expect(AuditLog::where('id', $old->id)->exists())->toBeFalse()
        ->and(AuditLog::where('id', $recent->id)->exists())->toBeTrue();
});
