<?php

use App\Models\Company;
use App\Models\SimCard;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

beforeEach(fn () => app(PermissionRegistrar::class)->forgetCachedPermissions());

test('redirects guests to the login page', function (string $method, string $uri) {
    $this->{$method}($uri)->assertRedirect('/login');
})->with([
    ['get', '/sim-cards'],
    ['get', '/sim-cards/create'],
    ['post', '/sim-cards'],
]);

test('returns 403 for non-super-admins', function (string $method, string $uri) {
    $this->actingAs(standardUser())->{$method}($uri)->assertForbidden();
})->with([
    ['get', '/sim-cards'],
    ['get', '/sim-cards/create'],
    ['post', '/sim-cards'],
]);

test('renders the sim cards list for super admins', function () {
    $simCard = SimCard::factory()->create(['number' => '+977-9812345678', 'provider' => 'ntc']);

    $this->actingAs(superAdminUser())->get('/sim-cards')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('sim-cards/index')
            ->has('simCards', 1)
            ->where('simCards.0.number', '+977-9812345678')
            ->where('simCards.0.provider', 'ntc')
            ->where('simCards.0.company.name', $simCard->company->name)
            ->where('filters.search', '')
            ->has('providers', 5)
            ->has('statuses', 3)
        );
});

test('renders the create page for super admins', function () {
    Company::factory()->create(['name' => 'Acme Corporation']);

    $this->actingAs(superAdminUser())->get('/sim-cards/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('sim-cards/create')
            ->has('companies', 1)
            ->has('providers', 5)
            ->has('statuses', 3)
        );
});

test('requires company, provider, number, and status when storing a sim card', function () {
    $this->actingAs(superAdminUser())->post('/sim-cards', [
        'company_id' => '',
        'provider' => '',
        'number' => '',
        'present_status' => '',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['company_id', 'provider', 'number', 'present_status']);

    expect(SimCard::count())->toBe(0);
});

test('rejects an unknown company, provider, number, and status', function () {
    SimCard::factory()->create(['number' => '+977-9812345678']);

    $this->actingAs(superAdminUser())->post('/sim-cards', [
        'company_id' => (string) Str::uuid(),
        'provider' => 'made-up',
        'number' => '+977-9812345678',
        'present_status' => 'made-up',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['company_id', 'provider', 'number', 'present_status']);

    expect(SimCard::count())->toBe(1);
});

test('stores a sim card and redirects to the list', function () {
    $company = Company::factory()->create();

    $this->actingAs(superAdminUser())->post('/sim-cards', [
        'company_id' => $company->id,
        'provider' => 'ncell',
        'number' => '+977-9812345678',
        'present_status' => 'assigned',
    ])->assertRedirect('/sim-cards');

    $simCard = SimCard::where('number', '+977-9812345678')->firstOrFail();
    expect($simCard->company_id)->toBe($company->id)
        ->and($simCard->provider->value)->toBe('ncell')
        ->and($simCard->present_status->value)->toBe('assigned');
});

test('searches and filters sim cards', function () {
    $acme = Company::factory()->create(['name' => 'Acme']);
    $other = Company::factory()->create(['name' => 'Other']);
    SimCard::factory()->create(['number' => '+977-9811111111', 'provider' => 'ntc', 'present_status' => 'assigned', 'company_id' => $acme->id]);
    SimCard::factory()->create(['number' => '+977-9822222222', 'provider' => 'ncell', 'present_status' => 'lost', 'company_id' => $other->id]);

    $this->actingAs(superAdminUser())->get('/sim-cards?search=9811111111')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('simCards', 1)
            ->where('simCards.0.provider', 'ntc')
        );

    $this->actingAs(superAdminUser())->get("/sim-cards?company_id={$other->id}&provider=ncell&status=lost")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('simCards', 1)
            ->where('simCards.0.number', '+977-9822222222')
        );

    $this->actingAs(superAdminUser())->get('/sim-cards?status=assigned')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('simCards', 1)
            ->where('simCards.0.number', '+977-9811111111')
        );
});
