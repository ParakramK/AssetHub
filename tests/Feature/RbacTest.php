<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

function adminUserWithPermissions(array $permissions): User
{
    foreach ($permissions as $name) {
        Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
    }

    $role = Role::firstOrCreate(
        ['name' => 'Admin', 'guard_name' => 'web'],
        ['description' => 'Standard Admin role'],
    );
    $role->givePermissionTo($permissions);

    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

test('grants an explicitly assigned permission and denies a missing one', function () {
    Permission::firstOrCreate(['name' => 'users.view', 'guard_name' => 'web']);

    $user = adminUserWithPermissions(['users.view']);

    expect($user->can('users.view'))->toBeTrue()
        ->and($user->can('users.delete'))->toBeFalse()
        ->and(Gate::forUser($user)->allows('users.view'))->toBeTrue()
        ->and(Gate::forUser($user)->denies('users.delete'))->toBeTrue();
});

test('superadmin passes every permission check including future permissions', function () {
    $user = superAdminUser();

    expect($user->can('users.view'))->toBeTrue()
        ->and($user->can('users.create'))->toBeTrue()
        ->and($user->can('users.delete'))->toBeTrue()
        ->and(Gate::forUser($user)->allows('users.delete'))->toBeTrue();

    // Created after the role assignment: no pivot row can exist for it.
    Permission::factory()->create(['name' => 'reports.export']);

    expect($user->can('reports.export'))->toBeTrue()
        ->and($user->roles()->first()->permissions()->count())->toBe(0);
});

test('a user with both a normal and a superadmin role has full access', function () {
    $user = adminUserWithPermissions(['users.view']);
    $user->assignRole(Role::factory()->superAdmin()->create());

    expect($user->isSuperAdmin())->toBeTrue()
        ->and($user->can('users.delete'))->toBeTrue();
});

test('removing the superadmin role falls back to normal RBAC permissions', function () {
    $user = superAdminUser();
    $editor = Role::factory()->create(['name' => 'Editor']);
    $editor->givePermissionTo(Permission::firstOrCreate(['name' => 'users.view', 'guard_name' => 'web']));
    $user->assignRole($editor);

    expect($user->can('users.delete'))->toBeTrue();

    $user->removeRole(Role::where('is_super_admin', true)->firstOrFail());

    expect($user->isSuperAdmin())->toBeFalse()
        ->and($user->can('users.view'))->toBeTrue()
        ->and($user->can('users.delete'))->toBeFalse();
});

test('direct permissions work without any role', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(Permission::firstOrCreate(['name' => 'users.view', 'guard_name' => 'web']));

    expect($user->can('users.view'))->toBeTrue()
        ->and($user->can('users.delete'))->toBeFalse();
});

test('guests are still unauthenticated and cannot pass permission gates', function () {
    $this->get('/roles')->assertRedirect('/login');

    expect(Gate::allows('users.view'))->toBeFalse();
});

test('superadmin passes permission middleware without any stored permission rows', function () {
    expect(DB::table('role_has_permissions')->count())->toBe(0);

    $this->actingAs(superAdminUser())->get('/roles')->assertOk();
    $this->actingAs(superAdminUser())->get('/roles/create')->assertOk();
    $this->actingAs(superAdminUser())->post('/roles', ['name' => 'Auditor'])->assertRedirect('/roles');

    $this->actingAs(standardUser())->get('/roles')->assertForbidden();
    $this->actingAs(standardUser())->post('/roles', ['name' => 'Auditor'])->assertForbidden();
});
