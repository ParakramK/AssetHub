<?php

use App\Enums\ServerType;
use App\Models\Server;
use App\Models\ServerCredential;
use App\Models\SshKey;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

beforeEach(fn () => app(PermissionRegistrar::class)->forgetCachedPermissions());

test('creates a server with relationships and an enum type', function () {
    $server = Server::factory()->create(['type' => ServerType::SSH]);

    expect($server->id)->toBeString()
        ->and($server->type)->toBe(ServerType::SSH)
        ->and($server->creator)->not->toBeNull()
        ->and($server->company)->not->toBeNull()
        ->and($server->company->servers()->where('id', $server->id)->exists())->toBeTrue()
        ->and($server->port)->toBeInt();
});

test('defaults the port from the server type when omitted', function (ServerType $type, int $port) {
    $server = Server::factory()->create(['type' => $type, 'port' => null]);

    expect($server->refresh()->port)->toBe($port);
})->with([
    [ServerType::RDP, 3389],
    [ServerType::SSH, 22],
    [ServerType::VNC, 5900],
    [ServerType::Telnet, 23],
]);

test('keeps an explicitly provided port', function () {
    $server = Server::factory()->create(['type' => ServerType::SSH, 'port' => 2222]);

    expect($server->refresh()->port)->toBe(2222);
});

test('encrypts secrets at rest and hides them from serialization', function () {
    $credential = ServerCredential::factory()->create(['password' => 'super-secret']);
    $key = SshKey::factory()->create(['private_key' => 'super-secret-key']);

    expect($credential->password)->toBe('super-secret')
        ->and($key->private_key)->toBe('super-secret-key')
        ->and(DB::table('server_credentials')->where('id', $credential->id)->value('password'))->not->toBe('super-secret')
        ->and(DB::table('ssh_keys')->where('id', $key->id)->value('private_key'))->not->toBe('super-secret-key')
        ->and($credential->toArray())->not->toHaveKey('password')
        ->and($key->toArray())->not->toHaveKey('private_key');
});

test('rejects a duplicate username on the same server', function () {
    $server = Server::factory()->create();
    ServerCredential::factory()->create(['server_id' => $server->id, 'username' => 'deploy']);

    ServerCredential::factory()->create(['server_id' => $server->id, 'username' => 'deploy']);
})->throws(QueryException::class);

test('deleting a server cascades to its credentials and ssh keys', function () {
    $server = Server::factory()->create();
    ServerCredential::factory()->create(['server_id' => $server->id]);
    SshKey::factory()->create(['server_id' => $server->id]);

    $server->delete();

    expect(ServerCredential::count())->toBe(0)
        ->and(SshKey::count())->toBe(0);
});

test('deleting a user keeps their servers but clears the creator', function () {
    $server = Server::factory()->create();

    $server->creator->delete();

    expect($server->refresh()->created_by)->toBeNull();
});

test('prevents deleting a company that owns servers', function () {
    $server = Server::factory()->create();

    $server->company->delete();
})->throws(QueryException::class);
