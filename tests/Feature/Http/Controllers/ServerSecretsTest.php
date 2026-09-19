<?php

use App\Models\Permission;
use App\Models\Server;
use App\Models\ServerCredential;
use App\Models\SshKey;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

beforeEach(fn () => app(PermissionRegistrar::class)->forgetCachedPermissions());

test('redirects guests to the login page', function (string $method, string $uri) {
    $server = Server::factory()->create();

    $this->{$method}(str_replace('{server}', $server->id, $uri))->assertRedirect('/login');
})->with([
    ['post', '/servers/{server}/credentials'],
    ['delete', '/servers/{server}/credentials/'.Str::uuid()],
    ['post', '/servers/{server}/ssh-keys'],
    ['delete', '/servers/{server}/ssh-keys/'.Str::uuid()],
]);

test('returns 403 for non-super-admins', function (string $method, string $uri) {
    $server = Server::factory()->create();

    $this->actingAs(standardUser())->{$method}(str_replace('{server}', $server->id, $uri))->assertForbidden();
})->with([
    ['post', '/servers/{server}/credentials'],
    ['delete', '/servers/{server}/credentials/'.Str::uuid()],
    ['post', '/servers/{server}/ssh-keys'],
    ['delete', '/servers/{server}/ssh-keys/'.Str::uuid()],
]);

test('renders the show page without exposing secrets', function () {
    $server = Server::factory()->create(['name' => 'Web 01']);
    ServerCredential::factory()->create(['server_id' => $server->id, 'username' => 'deploy']);
    $server->sshKeys()->create(['name' => 'Deploy key', 'private_key' => 'secret']);

    $this->actingAs(superAdminUser())->get("/servers/{$server->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('servers/show')
            ->where('server.name', 'Web 01')
            ->has('credentials', 1)
            ->where('credentials.0.username', 'deploy')
            ->missing('credentials.0.password')
            ->has('sshKeys', 1)
            ->where('sshKeys.0.name', 'Deploy key')
            ->missing('sshKeys.0.private_key')
            ->where('can.viewCredentials', true)
            ->where('can.viewSshKeys', true)
            ->where('can.createCredentials', true)
            ->where('can.deleteCredentials', true)
            ->where('can.createSshKeys', true)
            ->where('can.deleteSshKeys', true)
        );
});

test('stores a credential encrypted and redirects to the show page', function () {
    $server = Server::factory()->create();

    $this->actingAs(superAdminUser())->post("/servers/{$server->id}/credentials", [
        'username' => 'deploy',
        'password' => 'super-secret',
    ])->assertRedirect("/servers/{$server->id}");

    $credential = ServerCredential::where('server_id', $server->id)->firstOrFail();
    expect($credential->username)->toBe('deploy')
        ->and($credential->password)->toBe('super-secret')
        ->and(DB::table('server_credentials')->where('id', $credential->id)->value('password'))->not->toBe('super-secret');
});

test('rejects a duplicate username on the same server', function () {
    $server = Server::factory()->create();
    ServerCredential::factory()->create(['server_id' => $server->id, 'username' => 'deploy']);

    $this->actingAs(superAdminUser())->post("/servers/{$server->id}/credentials", [
        'username' => 'deploy',
        'password' => 'other-secret',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors('username');

    expect(ServerCredential::where('server_id', $server->id)->count())->toBe(1);
});

test('allows the same username on different servers', function () {
    $server = Server::factory()->create();
    $other = Server::factory()->create();
    ServerCredential::factory()->create(['server_id' => $other->id, 'username' => 'deploy']);

    $this->actingAs(superAdminUser())->post("/servers/{$server->id}/credentials", [
        'username' => 'deploy',
        'password' => 'super-secret',
    ])->assertRedirect("/servers/{$server->id}");

    expect(ServerCredential::where('server_id', $server->id)->count())->toBe(1);
});

test('deletes a credential and redirects to the show page', function () {
    $server = Server::factory()->create();
    $credential = ServerCredential::factory()->create(['server_id' => $server->id]);

    $this->actingAs(superAdminUser())->delete("/servers/{$server->id}/credentials/{$credential->id}")
        ->assertRedirect("/servers/{$server->id}");

    expect(ServerCredential::where('id', $credential->id)->exists())->toBeFalse();
});

test('returns 404 when deleting another server credential', function () {
    $server = Server::factory()->create();
    $other = Server::factory()->create();
    $credential = ServerCredential::factory()->create(['server_id' => $other->id]);

    $this->actingAs(superAdminUser())->delete("/servers/{$server->id}/credentials/{$credential->id}")
        ->assertNotFound();

    expect(ServerCredential::where('id', $credential->id)->exists())->toBeTrue();
});

test('stores an ssh key encrypted and redirects to the show page', function () {
    $server = Server::factory()->create();

    $this->actingAs(superAdminUser())->post("/servers/{$server->id}/ssh-keys", [
        'name' => 'Deploy key',
        'public_key' => 'ssh-ed25519 AAAA',
        'private_key' => 'super-secret-key',
    ])->assertRedirect("/servers/{$server->id}");

    $key = SshKey::where('server_id', $server->id)->firstOrFail();
    expect($key->name)->toBe('Deploy key')
        ->and($key->private_key)->toBe('super-secret-key')
        ->and(DB::table('ssh_keys')->where('id', $key->id)->value('private_key'))->not->toBe('super-secret-key');
});

test('rejects a duplicate ssh key name on the same server', function () {
    $server = Server::factory()->create();
    $server->sshKeys()->create(['name' => 'Deploy key', 'private_key' => 'secret']);

    $this->actingAs(superAdminUser())->post("/servers/{$server->id}/ssh-keys", [
        'name' => 'Deploy key',
        'private_key' => 'other-secret',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors('name');

    expect(SshKey::where('server_id', $server->id)->count())->toBe(1);
});

test('deletes an ssh key and redirects to the show page', function () {
    $server = Server::factory()->create();
    $key = $server->sshKeys()->create(['name' => 'Deploy key', 'private_key' => 'secret']);

    $this->actingAs(superAdminUser())->delete("/servers/{$server->id}/ssh-keys/{$key->id}")
        ->assertRedirect("/servers/{$server->id}");

    expect(SshKey::where('id', $key->id)->exists())->toBeFalse();
});

test('returns 404 when deleting another server ssh key', function () {
    $server = Server::factory()->create();
    $other = Server::factory()->create();
    $key = $other->sshKeys()->create(['name' => 'Deploy key', 'private_key' => 'secret']);

    $this->actingAs(superAdminUser())->delete("/servers/{$server->id}/ssh-keys/{$key->id}")
        ->assertNotFound();

    expect(SshKey::where('id', $key->id)->exists())->toBeTrue();
});

test('reveals a credential password to authorized users', function () {
    $server = Server::factory()->create();
    $credential = ServerCredential::factory()->create(['server_id' => $server->id, 'password' => 'super-secret']);

    $this->actingAs(superAdminUser())->getJson("/servers/{$server->id}/credentials/{$credential->id}/reveal")
        ->assertOk()
        ->assertJson(['password' => 'super-secret']);
});

test('reveals an ssh private key to authorized users', function () {
    $server = Server::factory()->create();
    $key = $server->sshKeys()->create(['name' => 'Deploy key', 'private_key' => 'super-secret-key']);

    $this->actingAs(superAdminUser())->getJson("/servers/{$server->id}/ssh-keys/{$key->id}/reveal")
        ->assertOk()
        ->assertJson(['private_key' => 'super-secret-key']);
});

test('denies reveal to guests and non-super-admins', function () {
    $server = Server::factory()->create();
    $credential = ServerCredential::factory()->create(['server_id' => $server->id]);
    $key = $server->sshKeys()->create(['name' => 'Deploy key', 'private_key' => 'secret']);

    $this->getJson("/servers/{$server->id}/credentials/{$credential->id}/reveal")->assertUnauthorized();
    $this->getJson("/servers/{$server->id}/ssh-keys/{$key->id}/reveal")->assertUnauthorized();

    $this->actingAs(standardUser())->getJson("/servers/{$server->id}/credentials/{$credential->id}/reveal")->assertForbidden();
    $this->actingAs(standardUser())->getJson("/servers/{$server->id}/ssh-keys/{$key->id}/reveal")->assertForbidden();
});

test('returns 404 when revealing another server secret', function () {
    $server = Server::factory()->create();
    $other = Server::factory()->create();
    $credential = ServerCredential::factory()->create(['server_id' => $other->id]);
    $key = $other->sshKeys()->create(['name' => 'Deploy key', 'private_key' => 'secret']);

    $this->actingAs(superAdminUser())->getJson("/servers/{$server->id}/credentials/{$credential->id}/reveal")->assertNotFound();
    $this->actingAs(superAdminUser())->getJson("/servers/{$server->id}/ssh-keys/{$key->id}/reveal")->assertNotFound();
});

test('allows reveal with the granular view permission independently per secret', function () {
    $server = Server::factory()->create();
    $credential = ServerCredential::factory()->create(['server_id' => $server->id, 'password' => 'super-secret']);
    $key = $server->sshKeys()->create(['name' => 'Deploy key', 'private_key' => 'super-secret-key']);

    $user = User::factory()->withoutDefaultRole()->create();
    $user->givePermissionTo(Permission::firstOrCreate(['name' => 'credentials.view', 'guard_name' => 'web']));

    $this->actingAs($user)->getJson("/servers/{$server->id}/credentials/{$credential->id}/reveal")
        ->assertOk()
        ->assertJson(['password' => 'super-secret']);

    $this->actingAs($user)->getJson("/servers/{$server->id}/ssh-keys/{$key->id}/reveal")
        ->assertForbidden();
});

test('denies reveal to users with only servers.view', function () {
    $server = Server::factory()->create();
    $credential = ServerCredential::factory()->create(['server_id' => $server->id]);

    $user = User::factory()->withoutDefaultRole()->create();
    $user->givePermissionTo(Permission::firstOrCreate(['name' => 'servers.view', 'guard_name' => 'web']));

    $this->actingAs($user)->get("/servers/{$server->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('can.viewCredentials', false)
            ->where('can.viewSshKeys', false)
            ->where('can.createCredentials', false)
            ->where('can.deleteCredentials', false)
            ->where('can.createSshKeys', false)
            ->where('can.deleteSshKeys', false)
        );

    $this->actingAs($user)->getJson("/servers/{$server->id}/credentials/{$credential->id}/reveal")
        ->assertForbidden();
});

test('separates credential management permissions granularly', function () {
    $server = Server::factory()->create();
    $existing = ServerCredential::factory()->create(['server_id' => $server->id]);

    $creator = User::factory()->withoutDefaultRole()->create();
    $creator->givePermissionTo(Permission::firstOrCreate(['name' => 'credentials.create', 'guard_name' => 'web']));

    $this->actingAs($creator)->post("/servers/{$server->id}/credentials", [
        'username' => 'deploy',
        'password' => 'secret',
    ])->assertRedirect("/servers/{$server->id}");

    $this->actingAs($creator)->delete("/servers/{$server->id}/credentials/{$existing->id}")
        ->assertForbidden();

    $destroyer = User::factory()->withoutDefaultRole()->create();
    $destroyer->givePermissionTo(Permission::firstOrCreate(['name' => 'credentials.delete', 'guard_name' => 'web']));

    $this->actingAs($destroyer)->post("/servers/{$server->id}/credentials", [
        'username' => 'other',
        'password' => 'secret',
    ])->assertForbidden();

    $this->actingAs($destroyer)->delete("/servers/{$server->id}/credentials/{$existing->id}")
        ->assertRedirect("/servers/{$server->id}");

    expect(ServerCredential::where('id', $existing->id)->exists())->toBeFalse();
});

test('separates ssh key management permissions granularly', function () {
    $server = Server::factory()->create();
    $existing = $server->sshKeys()->create(['name' => 'Old key', 'private_key' => 'secret']);

    $creator = User::factory()->withoutDefaultRole()->create();
    $creator->givePermissionTo(Permission::firstOrCreate(['name' => 'ssh-keys.create', 'guard_name' => 'web']));

    $this->actingAs($creator)->post("/servers/{$server->id}/ssh-keys", [
        'name' => 'New key',
        'private_key' => 'secret',
    ])->assertRedirect("/servers/{$server->id}");

    $this->actingAs($creator)->delete("/servers/{$server->id}/ssh-keys/{$existing->id}")
        ->assertForbidden();

    $destroyer = User::factory()->withoutDefaultRole()->create();
    $destroyer->givePermissionTo(Permission::firstOrCreate(['name' => 'ssh-keys.delete', 'guard_name' => 'web']));

    $this->actingAs($destroyer)->post("/servers/{$server->id}/ssh-keys", [
        'name' => 'Another key',
        'private_key' => 'secret',
    ])->assertForbidden();

    $this->actingAs($destroyer)->delete("/servers/{$server->id}/ssh-keys/{$existing->id}")
        ->assertRedirect("/servers/{$server->id}");

    expect(SshKey::where('id', $existing->id)->exists())->toBeFalse();
});

test('servers.update alone grants no secret access', function () {
    $server = Server::factory()->create();
    $credential = ServerCredential::factory()->create(['server_id' => $server->id]);

    $user = User::factory()->withoutDefaultRole()->create();
    $user->givePermissionTo(Permission::firstOrCreate(['name' => 'servers.update', 'guard_name' => 'web']));

    $this->actingAs($user)->post("/servers/{$server->id}/credentials", [
        'username' => 'deploy',
        'password' => 'secret',
    ])->assertForbidden();

    $this->actingAs($user)->delete("/servers/{$server->id}/credentials/{$credential->id}")
        ->assertForbidden();

    $this->actingAs($user)->getJson("/servers/{$server->id}/credentials/{$credential->id}/reveal")
        ->assertForbidden();
});
