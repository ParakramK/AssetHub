<?php

use App\Models\Company;
use App\Models\Domain;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

beforeEach(fn () => app(PermissionRegistrar::class)->forgetCachedPermissions());

test('redirects guests to the login page', function (string $method, string $uri) {
    $this->{$method}($uri)->assertRedirect('/login');
})->with([
    ['get', '/domains'],
    ['get', '/domains/create'],
    ['post', '/domains'],
]);

test('returns 403 for non-super-admins', function (string $method, string $uri) {
    $this->actingAs(standardUser())->{$method}($uri)->assertForbidden();
})->with([
    ['get', '/domains'],
    ['get', '/domains/create'],
    ['post', '/domains'],
]);

test('renders the domains list for super admins', function () {
    $company = Company::factory()->create(['name' => 'Acme Corporation']);
    Domain::factory()->create(['domain_name' => 'example.com', 'company_id' => $company->id]);

    $this->actingAs(superAdminUser())->get('/domains')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('domains/index')
            ->has('domains', 1)
            ->where('domains.0.domain_name', 'example.com')
            ->where('domains.0.company.name', 'Acme Corporation')
        );
});

test('renders the create page for super admins', function () {
    Company::factory()->create(['name' => 'Acme Corporation']);

    $this->actingAs(superAdminUser())->get('/domains/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('domains/create')
            ->has('companies', 1)
            ->where('companies.0.name', 'Acme Corporation')
        );
});

test('requires a company and domain name when storing a domain', function () {
    $this->actingAs(superAdminUser())->post('/domains', ['company_id' => '', 'domain_name' => ''])
        ->assertRedirect()
        ->assertSessionHasErrors(['company_id', 'domain_name']);

    expect(Domain::count())->toBe(0);
});

test('rejects an unknown company', function () {
    $this->actingAs(superAdminUser())->post('/domains', [
        'company_id' => (string) Str::uuid(),
        'domain_name' => 'example.com',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors('company_id');

    expect(Domain::count())->toBe(0);
});

test('rejects a duplicate domain name', function () {
    Domain::factory()->create(['domain_name' => 'example.com']);
    $company = Company::factory()->create();

    $this->actingAs(superAdminUser())->post('/domains', [
        'company_id' => $company->id,
        'domain_name' => 'example.com',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors('domain_name');
});

test('stores a domain crediting the authenticated user and redirects to the list', function () {
    $user = superAdminUser();
    $company = Company::factory()->create();

    $this->actingAs($user)->post('/domains', [
        'company_id' => $company->id,
        'domain_name' => 'example.com',
        'registrar' => 'Cloudflare',
        'expiry_date' => '2027-05-20',
    ])->assertRedirect('/domains');

    $domain = Domain::where('domain_name', 'example.com')->firstOrFail();
    expect($domain->company_id)->toBe($company->id)
        ->and($domain->created_by)->toBe($user->id)
        ->and($domain->registrar)->toBe('Cloudflare')
        ->and($domain->expiry_date->format('Y-m-d'))->toBe('2027-05-20');
});
