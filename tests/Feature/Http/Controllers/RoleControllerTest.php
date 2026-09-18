<?php

use App\Models\Role;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

function superAdminUser(): User
{
    $role = Role::factory()->superAdmin()->make();
    $user = User::factory()->make(['role_id' => $role->id]);
    $user->setRelation('role', $role);

    return $user;
}

function standardUser(): User
{
    $role = Role::factory()->make(['name' => 'User']);
    $user = User::factory()->make(['role_id' => $role->id]);
    $user->setRelation('role', $role);

    return $user;
}

test('redirects guests to the login page', function (string $method, string $uri) {
    $this->{$method}($uri)->assertRedirect('/login');
})->with([
    ['get', '/roles'],
    ['get', '/roles/create'],
    ['post', '/roles'],
]);

test('returns 403 for non-super-admins', function (string $method, string $uri) {
    $this->actingAs(standardUser())->{$method}($uri)->assertForbidden();
})->with([
    ['get', '/roles'],
    ['get', '/roles/create'],
    ['post', '/roles'],
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
