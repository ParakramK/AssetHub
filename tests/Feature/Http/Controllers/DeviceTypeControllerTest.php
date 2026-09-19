<?php

use App\Models\DeviceType;
use Database\Seeders\DeviceTypeSeeder;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

beforeEach(fn () => app(PermissionRegistrar::class)->forgetCachedPermissions());

test('redirects guests to the login page', function (string $method, string $uri) {
    $this->{$method}($uri)->assertRedirect('/login');
})->with([
    ['get', '/device-types'],
    ['get', '/device-types/create'],
    ['post', '/device-types'],
]);

test('returns 403 for non-super-admins', function (string $method, string $uri) {
    $this->actingAs(standardUser())->{$method}($uri)->assertForbidden();
})->with([
    ['get', '/device-types'],
    ['get', '/device-types/create'],
    ['post', '/device-types'],
]);

test('renders the device types list for super admins', function () {
    DeviceType::factory()->create(['name' => 'Laptop']);

    $this->actingAs(superAdminUser())->get('/device-types')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('device-types/index')
            ->has('types', 1)
        );
});

test('searches device types by name', function () {
    DeviceType::factory()->create(['name' => 'Laptop']);
    DeviceType::factory()->create(['name' => 'Monitor']);

    $this->actingAs(superAdminUser())->get('/device-types?search=lap')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('types', 1)
            ->where('types.0.name', 'Laptop')
            ->where('filters.search', 'lap')
        );
});

test('renders the create page for super admins', function () {
    $this->actingAs(superAdminUser())->get('/device-types/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('device-types/create'));
});

test('requires a unique name when storing a device type', function () {
    DeviceType::factory()->create(['name' => 'Laptop']);

    $this->actingAs(superAdminUser())->post('/device-types', ['name' => ''])
        ->assertRedirect()
        ->assertSessionHasErrors('name');

    $this->actingAs(superAdminUser())->post('/device-types', ['name' => 'Laptop'])
        ->assertRedirect()
        ->assertSessionHasErrors('name');

    expect(DeviceType::count())->toBe(1);
});

test('stores a device type and redirects to the list', function () {
    $this->actingAs(superAdminUser())->post('/device-types', ['name' => 'Tablet'])
        ->assertRedirect('/device-types');

    expect(DeviceType::where('name', 'Tablet')->exists())->toBeTrue();
});

test('seeds the standard device types idempotently', function () {
    app(DeviceTypeSeeder::class)->run();
    app(DeviceTypeSeeder::class)->run();

    expect(DeviceType::count())->toBe(6)
        ->and(DeviceType::where('name', 'Mobile Phone')->exists())->toBeTrue();
});
