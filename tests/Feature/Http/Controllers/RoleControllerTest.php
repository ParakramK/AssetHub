<?php

use App\Enums\PermissionName;
use App\Models\Permission;
use App\Models\Role;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

beforeEach(fn () => app(PermissionRegistrar::class)->forgetCachedPermissions());

test('redirects guests to the login page', function (string $method, string $uri) {
    if (str_contains($uri, '{role}')) {
        $uri = str_replace('{role}', Role::factory()->create()->id, $uri);
    }

    $this->{$method}($uri)->assertRedirect('/login');
})->with([
    ['get', '/roles'],
    ['get', '/roles/create'],
    ['post', '/roles'],
    ['get', '/roles/{role}/edit'],
    ['put', '/roles/{role}'],
]);

test('returns 403 for non-super-admins', function (string $method, string $uri) {
    if (str_contains($uri, '{role}')) {
        $uri = str_replace('{role}', Role::factory()->create()->id, $uri);
    }

    $this->actingAs(standardUser())->{$method}($uri)->assertForbidden();
})->with([
    ['get', '/roles'],
    ['get', '/roles/create'],
    ['post', '/roles'],
    ['get', '/roles/{role}/edit'],
    ['put', '/roles/{role}'],
]);

test('renders the roles list for super admins', function () {
    Role::factory()->create(['name' => 'Admin']);
    Role::factory()->superAdmin()->create();

    $this->actingAs(superAdminUser())->get('/roles')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('roles/index')
            ->has('roles', 2)
            ->where('roles.0.name', 'Admin')
            ->where('roles.1.name', 'Super Admin')
        );
});

test('renders the create page for super admins', function () {
    $this->actingAs(superAdminUser())->get('/roles/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('roles/create'));
});

test('requires a name when storing a role', function () {
    $this->actingAs(superAdminUser())->post('/roles', ['name' => ''])
        ->assertRedirect()
        ->assertSessionHasErrors('name');

    expect(Role::where('name', '')->exists())->toBeFalse();
});

test('rejects a duplicate role name', function () {
    Role::factory()->create(['name' => 'Admin']);

    $this->actingAs(superAdminUser())->post('/roles', ['name' => 'Admin'])
        ->assertRedirect()
        ->assertSessionHasErrors('name');
});

test('stores a role and redirects to the list', function () {
    $this->actingAs(superAdminUser())->post('/roles', [
        'name' => 'Asset Manager',
        'description' => 'Manages company assets',
    ])->assertRedirect('/roles');

    $role = Role::where('name', 'Asset Manager')->firstOrFail();
    expect($role->description)->toBe('Manages company assets')
        ->and($role->is_super_admin)->toBeFalse();
});

test('renders the permission assignment page for super admins', function () {
    $role = Role::factory()->create(['name' => 'Editor']);
    $role->givePermissionTo(
        Permission::firstOrCreate(['name' => 'users.view', 'guard_name' => 'web'])
    );

    $expectedModules = PermissionName::grouped();

    $this->actingAs(superAdminUser())->get("/roles/{$role->id}/edit")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('roles/edit')
            ->where('role.name', 'Editor')
            ->where('rolePermissions', ['users.view'])
            ->has('modules', count($expectedModules))
            ->where('modules.0.name', 'users')
            ->where('modules.1.name', 'roles')
        );
});

test('syncs permissions when updating a role', function () {
    Permission::firstOrCreate(['name' => 'users.view', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'companies.create', 'guard_name' => 'web']);
    $role = Role::factory()->create(['name' => 'Editor']);

    $this->actingAs(superAdminUser())->put("/roles/{$role->id}", [
        'permissions' => ['users.view', 'companies.create'],
    ])->assertRedirect('/roles');

    expect($role->refresh()->hasPermissionTo('users.view'))->toBeTrue()
        ->and($role->hasPermissionTo('companies.create'))->toBeTrue()
        ->and($role->permissions()->count())->toBe(2);
});

test('clears all permissions when updating with an empty selection', function () {
    $role = Role::factory()->create(['name' => 'Editor']);
    $role->givePermissionTo(
        Permission::firstOrCreate(['name' => 'users.view', 'guard_name' => 'web'])
    );

    $this->actingAs(superAdminUser())->put("/roles/{$role->id}", [
        'permissions' => [],
    ])->assertRedirect('/roles');

    expect($role->refresh()->permissions()->count())->toBe(0);
});

test('rejects unknown permission values when updating a role', function () {
    $role = Role::factory()->create(['name' => 'Editor']);

    $this->actingAs(superAdminUser())->put("/roles/{$role->id}", [
        'permissions' => ['users.view', 'made.up.permission'],
    ])
        ->assertRedirect()
        ->assertSessionHasErrors('permissions.1');

    expect($role->refresh()->permissions()->count())->toBe(0);
});

test('creates the permission row when assigning a permission missing from the database', function () {
    $role = Role::factory()->create(['name' => 'Editor']);

    expect(Permission::where('name', 'domains.delete')->exists())->toBeFalse();

    $this->actingAs(superAdminUser())->put("/roles/{$role->id}", [
        'permissions' => ['domains.delete'],
    ])->assertRedirect('/roles');

    expect(Permission::where('name', 'domains.delete')->exists())->toBeTrue()
        ->and($role->refresh()->hasPermissionTo('domains.delete'))->toBeTrue();
});
