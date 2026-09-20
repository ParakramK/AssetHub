<?php

use App\Models\Company;
use App\Models\Domain;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Spatie\Permission\PermissionRegistrar;

beforeEach(fn () => app(PermissionRegistrar::class)->forgetCachedPermissions());

test('creates a domain with a uuid id linked to a company and creator', function () {
    $domain = Domain::factory()->create();

    expect($domain->id)->toBeString()
        ->and($domain->company)->toBeInstanceOf(Company::class)
        ->and($domain->creator)->not->toBeNull()
        ->and($domain->expiry_date)->toBeInstanceOf(Carbon::class);
});

test('expiry date is cast to a date without time', function () {
    $domain = Domain::factory()->create(['expiry_date' => '2027-05-20']);

    expect($domain->refresh()->expiry_date->format('Y-m-d'))->toBe('2027-05-20');
});

test('registrar and expiry date are optional', function () {
    $domain = Domain::factory()->create(['registrar' => null, 'expiry_date' => null]);

    expect($domain->refresh()->registrar)->toBeNull()
        ->and($domain->expiry_date)->toBeNull();
});

test('rejects a duplicate domain name', function () {
    Domain::factory()->create(['domain_name' => 'example.com']);

    Domain::factory()->create(['domain_name' => 'example.com']);
})->throws(QueryException::class);

test('prevents deleting a company that owns domains', function () {
    $domain = Domain::factory()->create();

    $domain->company->delete();
})->throws(QueryException::class);

test('updates a domain expiry date after renewal', function () {
    $domain = Domain::factory()->create(['expiry_date' => '2026-01-01']);

    $this->actingAs(superAdminUser())
        ->get("/domains/{$domain->id}/edit")
        ->assertOk();

    $this->actingAs(superAdminUser())
        ->put("/domains/{$domain->id}", [
            'company_id' => $domain->company_id,
            'domain_name' => $domain->domain_name,
            'registrar' => $domain->registrar,
            'expiry_date' => '2027-01-01',
        ])
        ->assertRedirect('/domains');

    expect($domain->refresh()->expiry_date->format('Y-m-d'))->toBe('2027-01-01');
});

test('forbids domain edit without the update permission', function () {
    $domain = Domain::factory()->create();

    $this->actingAs(standardUser())
        ->get("/domains/{$domain->id}/edit")
        ->assertForbidden();

    $this->actingAs(standardUser())
        ->put("/domains/{$domain->id}", [
            'company_id' => $domain->company_id,
            'domain_name' => $domain->domain_name,
            'expiry_date' => '2027-01-01',
        ])
        ->assertForbidden();
});

test('rejects a duplicate domain name on update but keeps the unchanged name', function () {
    Domain::factory()->create(['domain_name' => 'taken.com']);
    $domain = Domain::factory()->create(['domain_name' => 'mine.com']);

    $this->actingAs(superAdminUser())
        ->put("/domains/{$domain->id}", [
            'company_id' => $domain->company_id,
            'domain_name' => 'taken.com',
        ])
        ->assertSessionHasErrors('domain_name');

    $this->actingAs(superAdminUser())
        ->put("/domains/{$domain->id}", [
            'company_id' => $domain->company_id,
            'domain_name' => 'mine.com',
            'expiry_date' => '2027-06-30',
        ])
        ->assertRedirect('/domains');

    expect($domain->refresh()->domain_name)->toBe('mine.com');
});
