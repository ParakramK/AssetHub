<?php

use App\Models\Company;
use Inertia\Testing\AssertableInertia as Assert;

test('redirects guests to the login page', function (string $method, string $uri) {
    $this->{$method}($uri)->assertRedirect('/login');
})->with([
    ['get', '/companies'],
    ['get', '/companies/create'],
    ['post', '/companies'],
]);

test('returns 403 for non-super-admins', function (string $method, string $uri) {
    $this->actingAs(standardUser())->{$method}($uri)->assertForbidden();
})->with([
    ['get', '/companies'],
    ['get', '/companies/create'],
    ['post', '/companies'],
]);

test('renders the companies list for super admins', function () {
    Company::factory()->create(['name' => 'Beta Corp']);
    Company::factory()->create(['name' => 'Acme Corporation']);

    $this->actingAs(superAdminUser())->get('/companies')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('companies/index')
            ->has('companies', 2)
            ->where('companies.0.name', 'Acme Corporation')
            ->where('companies.1.name', 'Beta Corp')
        );
});

test('renders the create page for super admins', function () {
    $this->actingAs(superAdminUser())->get('/companies/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('companies/create'));
});

test('requires a name when storing a company', function () {
    $this->actingAs(superAdminUser())->post('/companies', ['name' => ''])
        ->assertRedirect()
        ->assertSessionHasErrors('name');

    expect(Company::where('name', '')->exists())->toBeFalse();
});

test('rejects a duplicate company name', function () {
    Company::factory()->create(['name' => 'Acme Corporation']);

    $this->actingAs(superAdminUser())->post('/companies', ['name' => 'Acme Corporation'])
        ->assertRedirect()
        ->assertSessionHasErrors('name');
});

test('stores a company and redirects to the list', function () {
    $this->actingAs(superAdminUser())->post('/companies', [
        'name' => 'Acme Corporation',
        'address' => '123 Main St',
        'phone' => '+1 (555) 000-0000',
        'email' => 'contact@acme.test',
    ])->assertRedirect('/companies');

    $company = Company::where('name', 'Acme Corporation')->firstOrFail();
    expect($company->address)->toBe('123 Main St')
        ->and($company->phone)->toBe('+1 (555) 000-0000')
        ->and($company->email)->toBe('contact@acme.test');
});

test('stores a company with only a name', function () {
    $this->actingAs(superAdminUser())->post('/companies', [
        'name' => 'Acme Corporation',
    ])->assertRedirect('/companies');

    $company = Company::where('name', 'Acme Corporation')->firstOrFail();
    expect($company->address)->toBeNull()
        ->and($company->phone)->toBeNull()
        ->and($company->email)->toBeNull();
});
