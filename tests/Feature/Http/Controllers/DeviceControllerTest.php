<?php

use App\Models\Company;
use App\Models\Device;
use App\Models\DeviceType;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

beforeEach(fn () => app(PermissionRegistrar::class)->forgetCachedPermissions());

test('redirects guests to the login page', function (string $method, string $uri) {
    $this->{$method}($uri)->assertRedirect('/login');
})->with([
    ['get', '/devices'],
    ['get', '/devices/create'],
    ['post', '/devices'],
]);

test('returns 403 for non-super-admins', function (string $method, string $uri) {
    $this->actingAs(standardUser())->{$method}($uri)->assertForbidden();
})->with([
    ['get', '/devices'],
    ['get', '/devices/create'],
    ['post', '/devices'],
]);

test('renders the devices list for super admins', function () {
    $device = Device::factory()->create(['brand' => 'Dell', 'model' => 'Latitude']);

    $this->actingAs(superAdminUser())->get('/devices')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('devices/index')
            ->has('devices', 1)
            ->where('devices.0.brand', 'Dell')
            ->where('devices.0.type.name', $device->type->name)
            ->where('devices.0.company.name', $device->company->name)
            ->where('filters.search', '')
            ->has('statuses', 4)
        );
});

test('renders the create page for super admins', function () {
    Company::factory()->create(['name' => 'Acme Corporation']);
    DeviceType::factory()->create(['name' => 'Laptop']);

    $this->actingAs(superAdminUser())->get('/devices/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('devices/create')
            ->has('companies', 1)
            ->has('types', 1)
        );
});

test('requires company, type, brand, and model when storing a device', function () {
    $this->actingAs(superAdminUser())->post('/devices', [
        'company_id' => '',
        'device_type_id' => '',
        'brand' => '',
        'model' => '',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['company_id', 'device_type_id', 'brand', 'model']);

    expect(Device::count())->toBe(0);
});

test('rejects unknown references and duplicate identifiers', function () {
    Device::factory()->create(['serial_no' => 'SN-001']);

    $this->actingAs(superAdminUser())->post('/devices', [
        'company_id' => (string) Str::uuid(),
        'device_type_id' => (string) Str::uuid(),
        'brand' => 'Dell',
        'model' => 'Latitude',
        'serial_no' => 'SN-001',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['company_id', 'device_type_id', 'serial_no']);

    expect(Device::count())->toBe(1);
});

test('stores a device with a generated code and available status', function () {
    $company = Company::factory()->create();
    $type = DeviceType::factory()->create(['name' => 'Laptop']);

    $this->actingAs(superAdminUser())->post('/devices', [
        'company_id' => $company->id,
        'device_type_id' => $type->id,
        'brand' => 'Dell',
        'model' => 'Latitude 5440',
        'serial_no' => 'SN-123',
    ])->assertRedirect('/devices');

    $device = Device::where('serial_no', 'SN-123')->firstOrFail();
    expect($device->code)->toStartWith('AST-')
        ->and($device->status->value)->toBe('available')
        ->and($device->current_employee_id)->toBeNull();
});

test('generates unique codes for every device', function () {
    $devices = Device::factory()->count(3)->create();

    expect($devices->pluck('code')->unique())->toHaveCount(3);
});

test('searches and filters devices', function () {
    $acme = Company::factory()->create(['name' => 'Acme']);
    $laptop = DeviceType::factory()->create(['name' => 'Laptop']);
    $monitor = DeviceType::factory()->create(['name' => 'Monitor']);
    Device::factory()->create(['brand' => 'Dell', 'model' => 'Latitude', 'serial_no' => 'SN-1', 'company_id' => $acme->id, 'device_type_id' => $laptop->id]);
    Device::factory()->create(['brand' => 'Samsung', 'model' => 'S24', 'serial_no' => 'SN-2', 'company_id' => $acme->id, 'device_type_id' => $monitor->id]);

    $this->actingAs(superAdminUser())->get('/devices?search=dell')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('devices', 1)
            ->where('devices.0.model', 'Latitude')
        );

    $this->actingAs(superAdminUser())->get("/devices?type_id={$monitor->id}&status=available")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('devices', 1)
            ->where('devices.0.model', 'S24')
        );

    $this->actingAs(superAdminUser())->get("/devices?company_id={$acme->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->has('devices', 2));
});
