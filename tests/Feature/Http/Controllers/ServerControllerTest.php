<?php

use App\Models\Company;
use App\Models\Server;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

beforeEach(fn () => app(PermissionRegistrar::class)->forgetCachedPermissions());

test('redirects guests to the login page', function (string $method, string $uri) {
    $this->{$method}($uri)->assertRedirect('/login');
})->with([
    ['get', '/servers'],
    ['get', '/servers/create'],
    ['post', '/servers'],
]);

test('returns 403 for non-super-admins', function (string $method, string $uri) {
    $this->actingAs(standardUser())->{$method}($uri)->assertForbidden();
})->with([
    ['get', '/servers'],
    ['get', '/servers/create'],
    ['post', '/servers'],
]);

test('renders the servers list for super admins', function () {
    $server = Server::factory()->create(['name' => 'Web 01', 'type' => 'ssh', 'ip_address' => '192.168.1.10']);

    $this->actingAs(superAdminUser())->get('/servers')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('servers/index')
            ->has('servers', 1)
            ->where('servers.0.name', 'Web 01')
            ->where('servers.0.ip_address', '192.168.1.10')
            ->where('servers.0.type', 'ssh')
            ->where('servers.0.company.name', $server->company->name)
            ->where('servers.0.creator.name', $server->creator->name)
        );
});

test('renders the create page for super admins', function () {
    Company::factory()->create(['name' => 'Acme Corporation']);

    $this->actingAs(superAdminUser())->get('/servers/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('servers/create')
            ->has('companies', 1)
            ->where('companies.0.name', 'Acme Corporation')
            ->has('types', 4)
            ->where('types.1.value', 'ssh')
            ->where('types.1.defaultPort', 22)
        );
});

test('requires company, name, type, and ip address when storing a server', function () {
    $this->actingAs(superAdminUser())->post('/servers', [
        'company_id' => '',
        'name' => '',
        'type' => '',
        'ip_address' => '',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['company_id', 'name', 'type', 'ip_address']);

    expect(Server::count())->toBe(0);
});

test('rejects an unknown company, type, and port when storing a server', function () {
    $companyId = Company::factory()->create()->id;

    $cases = [
        [['company_id' => (string) Str::uuid(), 'name' => 'Web 01', 'type' => 'ssh', 'ip_address' => '192.168.1.10'], 'company_id'],
        [['company_id' => $companyId, 'name' => 'Web 01', 'type' => 'made-up', 'ip_address' => '192.168.1.10'], 'type'],
        [['company_id' => $companyId, 'name' => 'Web 01', 'type' => 'ssh', 'ip_address' => '192.168.1.10', 'port' => 99999], 'port'],
    ];

    foreach ($cases as [$payload, $field]) {
        $this->actingAs(superAdminUser())->post('/servers', $payload)
            ->assertRedirect()
            ->assertSessionHasErrors($field);
    }

    expect(Server::count())->toBe(0);
});

test('stores a server crediting the authenticated user and redirects to the list', function () {
    $user = superAdminUser();
    $company = Company::factory()->create();

    $this->actingAs($user)->post('/servers', [
        'company_id' => $company->id,
        'name' => 'Web 01',
        'type' => 'rdp',
        'ip_address' => '10.0.0.5',
        'port' => 3390,
    ])->assertRedirect('/servers');

    $server = Server::where('ip_address', '10.0.0.5')->firstOrFail();
    expect($server->company_id)->toBe($company->id)
        ->and($server->created_by)->toBe($user->id)
        ->and($server->name)->toBe('Web 01')
        ->and($server->type->value)->toBe('rdp')
        ->and($server->port)->toBe(3390);
});

test('stores a server with the default port when omitted', function () {
    $this->actingAs(superAdminUser())->post('/servers', [
        'company_id' => Company::factory()->create()->id,
        'name' => 'Web 01',
        'type' => 'ssh',
        'ip_address' => '192.168.1.10',
    ])->assertRedirect('/servers');

    expect(Server::where('ip_address', '192.168.1.10')->firstOrFail()->port)->toBe(22);
});

test('searches servers by name or ip address', function () {
    Server::factory()->create(['name' => 'Web 01', 'ip_address' => '192.168.1.10']);
    Server::factory()->create(['name' => 'DB 01', 'ip_address' => '10.0.0.5']);

    $this->actingAs(superAdminUser())->get('/servers?search=web')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('servers/index')
            ->has('servers', 1)
            ->where('servers.0.name', 'Web 01')
            ->where('filters.search', 'web')
        );

    $this->actingAs(superAdminUser())->get('/servers?search=10.0.0')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('servers', 1)
            ->where('servers.0.name', 'DB 01')
        );
});

test('filters servers by company and type', function () {
    $acme = Company::factory()->create(['name' => 'Acme']);
    $other = Company::factory()->create(['name' => 'Other']);
    Server::factory()->create(['name' => 'Web 01', 'type' => 'ssh', 'company_id' => $acme->id]);
    Server::factory()->create(['name' => 'Win 01', 'type' => 'rdp', 'company_id' => $acme->id]);
    Server::factory()->create(['name' => 'Other 01', 'type' => 'ssh', 'company_id' => $other->id]);

    $this->actingAs(superAdminUser())->get("/servers?company_id={$acme->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('servers', 2)
            ->where('filters.company_id', $acme->id)
        );

    $this->actingAs(superAdminUser())->get('/servers?type=rdp')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('servers', 1)
            ->where('servers.0.name', 'Win 01')
            ->where('filters.type', 'rdp')
        );

    $this->actingAs(superAdminUser())->get("/servers?company_id={$acme->id}&type=ssh")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('servers', 1)
            ->where('servers.0.name', 'Web 01')
        );
});

test('ignores invalid filter values instead of failing', function () {
    Server::factory()->create(['name' => 'Web 01']);

    $this->actingAs(superAdminUser())->get('/servers?type=made-up&company_id=nope')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('servers', 1)
            ->where('filters.type', null)
            ->where('filters.company_id', null)
        );
});
