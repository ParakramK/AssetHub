<?php

use App\Models\Server;
use App\Models\ServerCredential;
use App\Models\SshKey;
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
