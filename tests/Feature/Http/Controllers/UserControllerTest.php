<?php

use App\Models\Domain;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Server;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

beforeEach(fn () => app(PermissionRegistrar::class)->forgetCachedPermissions());

test('redirects guests to the login page', function (string $method, string $uri) {
    $this->{$method}($uri)->assertRedirect('/login');
})->with([
    ['get', '/users'],
    ['get', '/users/create'],
    ['post', '/users'],
]);

test('returns 403 for non-super-admins', function (string $method, string $uri) {
    $this->actingAs(standardUser())->{$method}($uri)->assertForbidden();
})->with([
    ['get', '/users'],
    ['get', '/users/create'],
    ['post', '/users'],
]);

test('renders the users list for super admins', function () {
    $user = superAdminUser();

    $this->actingAs($user)->get('/users')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('users/index')
            ->has('users', 1)
            ->where('users.0.email', $user->email)
            ->where('users.0.roles.0.name', 'Super Admin')
        );
});

test('renders the create page for super admins', function () {
    Role::factory()->create(['name' => 'Editor']);

    $this->actingAs(superAdminUser())->get('/users/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('users/create')
            ->has('roles', 2)
        );
});

test('requires name, email, and confirmed password when storing a user', function () {
    $this->actingAs(superAdminUser())->post('/users', [
        'name' => '',
        'email' => 'not-an-email',
        'password' => 'secret',
        'password_confirmation' => 'mismatch',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['name', 'email', 'password']);

    expect(User::where('email', 'not-an-email')->exists())->toBeFalse();
});

test('rejects a duplicate email and an unknown role', function () {
    User::factory()->create(['email' => 'jane@example.com']);

    $this->actingAs(superAdminUser())->post('/users', [
        'name' => 'Jane',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'roles' => [(string) Str::uuid()],
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['email', 'roles.0']);

    expect(User::where('name', 'Jane')->exists())->toBeFalse();
});

test('stores a user with a hashed password and assigned roles', function () {
    $editor = Role::factory()->create(['name' => 'Editor']);

    $this->actingAs(superAdminUser())->post('/users', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'roles' => [$editor->id],
    ])->assertRedirect('/users');

    $user = User::where('email', 'jane@example.com')->firstOrFail();
    expect(Hash::check('password123', $user->password))->toBeTrue()
        ->and($user->hasRole('Editor'))->toBeTrue();
});

test('stores a user with no roles when none are selected', function () {
    $this->actingAs(superAdminUser())->post('/users', [
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password123',
        'password_confirmation' => 'password123',
        'roles' => [],
    ])->assertRedirect('/users');

    expect(User::where('email', 'jane@example.com')->firstOrFail()->roles()->count())->toBe(0);
});

test('redirects guests when deleting a user', function () {
    $user = User::factory()->create();

    $this->delete("/users/{$user->id}")->assertRedirect('/login');

    expect(User::where('id', $user->id)->exists())->toBeTrue();
});

test('returns 403 for non-super-admins deleting a user', function () {
    $user = User::factory()->create();

    $this->actingAs(standardUser())->delete("/users/{$user->id}")->assertForbidden();

    expect(User::where('id', $user->id)->exists())->toBeTrue();
});

test('prevents deleting your own account', function () {
    $user = superAdminUser();

    $this->actingAs($user)->delete("/users/{$user->id}")->assertForbidden();

    expect(User::where('id', $user->id)->exists())->toBeTrue();
});

test('deletes a user, clears creatorship, and removes assignments', function () {
    $user = User::factory()->withoutDefaultRole()->create();
    $user->assignRole(Role::factory()->create(['name' => 'Editor']));
    $user->givePermissionTo(Permission::firstOrCreate(['name' => 'users.view', 'guard_name' => 'web']));
    $server = Server::factory()->create(['created_by' => $user->id]);
    $domain = Domain::factory()->create(['created_by' => $user->id]);

    $this->actingAs(superAdminUser())->delete("/users/{$user->id}")
        ->assertRedirect('/users');

    expect(User::where('id', $user->id)->exists())->toBeFalse()
        ->and(DB::table('model_has_roles')->where('model_id', $user->id)->count())->toBe(0)
        ->and(DB::table('model_has_permissions')->where('model_id', $user->id)->count())->toBe(0)
        ->and($server->refresh()->created_by)->toBeNull()
        ->and($domain->refresh()->created_by)->toBeNull()
        ->and(Server::where('id', $server->id)->exists())->toBeTrue()
        ->and(Domain::where('id', $domain->id)->exists())->toBeTrue();
});

test('lists impact counts on the index', function () {
    $user = superAdminUser();
    Server::factory()->create(['created_by' => $user->id]);
    Domain::factory()->create(['created_by' => $user->id]);

    $this->actingAs($user)->get('/users')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('users/index')
            ->has('users', 1)
            ->where('users.0.created_servers_count', 1)
            ->where('users.0.created_domains_count', 1)
        );
});
