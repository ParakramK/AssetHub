<?php

use App\Models\Device;
use App\Models\DeviceAssignment;
use App\Models\Employee;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

beforeEach(fn () => app(PermissionRegistrar::class)->forgetCachedPermissions());

test('redirects guests to the login page', function () {
    $employee = Employee::factory()->create();
    $assignment = DeviceAssignment::factory()->create();

    $this->post("/employees/{$employee->id}/device-assignments", [])->assertRedirect('/login');
    $this->post("/device-assignments/{$assignment->id}/return")->assertRedirect('/login');
});

test('returns 403 for non-super-admins', function () {
    $employee = Employee::factory()->create();
    $assignment = DeviceAssignment::factory()->create();

    $user = standardUser();
    $this->actingAs($user)->post("/employees/{$employee->id}/device-assignments", [])->assertForbidden();
    $this->actingAs($user)->post("/device-assignments/{$assignment->id}/return")->assertForbidden();
});

test('shows current devices and history on the employee page', function () {
    $employee = Employee::factory()->create();
    $device = Device::factory()->create(['company_id' => $employee->company_id, 'brand' => 'Dell']);
    DeviceAssignment::factory()->create([
        'device_id' => $device->id,
        'employee_id' => $employee->id,
    ]);
    $device->update(['current_employee_id' => $employee->id]);

    $this->actingAs(superAdminUser())->get("/employees/{$employee->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('employees/show')
            ->has('currentDevices', 1)
            ->where('currentDevices.0.brand', 'Dell')
            ->has('deviceHistory', 1)
        );
});

test('assigns a device and syncs holder and status', function () {
    $employee = Employee::factory()->create();
    $device = Device::factory()->create(['company_id' => $employee->company_id, 'status' => 'available']);

    $this->actingAs(superAdminUser())->post("/employees/{$employee->id}/device-assignments", [
        'device_id' => $device->id,
    ])->assertRedirect("/employees/{$employee->id}");

    $device->refresh();
    expect($device->current_employee_id)->toBe($employee->id)
        ->and($device->status->value)->toBe('assigned')
        ->and(DeviceAssignment::where('device_id', $device->id)->whereNull('returned_at')->count())->toBe(1);
});

test('rejects assigning an already assigned device', function () {
    $employee = Employee::factory()->create();
    $other = Employee::factory()->create(['company_id' => $employee->company_id]);
    $device = Device::factory()->create(['company_id' => $employee->company_id, 'current_employee_id' => $other->id]);

    $this->actingAs(superAdminUser())->post("/employees/{$employee->id}/device-assignments", [
        'device_id' => $device->id,
    ])
        ->assertRedirect()
        ->assertSessionHasErrors('device_id');

    expect(DeviceAssignment::where('device_id', $device->id)->count())->toBe(0);
});

test('rejects assigning a device from another company', function () {
    $employee = Employee::factory()->create();
    $device = Device::factory()->create();

    $this->actingAs(superAdminUser())->post("/employees/{$employee->id}/device-assignments", [
        'device_id' => $device->id,
    ])
        ->assertRedirect()
        ->assertSessionHasErrors('device_id');

    expect(DeviceAssignment::where('device_id', $device->id)->count())->toBe(0);
});

test('rejects an unknown device', function () {
    $employee = Employee::factory()->create();

    $this->actingAs(superAdminUser())->post("/employees/{$employee->id}/device-assignments", [
        'device_id' => (string) Str::uuid(),
    ])
        ->assertRedirect()
        ->assertSessionHasErrors('device_id');
});

test('returns a device and keeps the history row', function () {
    $employee = Employee::factory()->create();
    $device = Device::factory()->create(['company_id' => $employee->company_id]);
    $assignment = DeviceAssignment::factory()->create([
        'device_id' => $device->id,
        'employee_id' => $employee->id,
    ]);
    $device->update(['current_employee_id' => $employee->id, 'status' => 'assigned']);

    $this->actingAs(superAdminUser())->post("/device-assignments/{$assignment->id}/return")
        ->assertRedirect();

    $device->refresh();
    expect($assignment->refresh()->returned_at)->not->toBeNull()
        ->and($device->current_employee_id)->toBeNull()
        ->and($device->status->value)->toBe('available')
        ->and(DeviceAssignment::where('device_id', $device->id)->count())->toBe(1);
});

test('rejects returning an already closed assignment', function () {
    $assignment = DeviceAssignment::factory()->returned()->create();

    $this->actingAs(superAdminUser())->post("/device-assignments/{$assignment->id}/return")
        ->assertStatus(422);
});

test('maintains full history across reassignments', function () {
    $jane = Employee::factory()->create();
    $john = Employee::factory()->create(['company_id' => $jane->company_id]);
    $device = Device::factory()->create(['company_id' => $jane->company_id]);

    $this->actingAs(superAdminUser())->post("/employees/{$jane->id}/device-assignments", ['device_id' => $device->id])
        ->assertRedirect();
    $first = DeviceAssignment::where('device_id', $device->id)->whereNull('returned_at')->firstOrFail();

    $this->actingAs(superAdminUser())->post("/device-assignments/{$first->id}/return")->assertRedirect();

    $this->actingAs(superAdminUser())->post("/employees/{$john->id}/device-assignments", ['device_id' => $device->id])
        ->assertRedirect();

    $history = DeviceAssignment::where('device_id', $device->id)->orderBy('assigned_at')->get();
    expect($history)->toHaveCount(2)
        ->and($history[0]->employee_id)->toBe($jane->id)
        ->and($history[0]->returned_at)->not->toBeNull()
        ->and($history[1]->employee_id)->toBe($john->id)
        ->and($history[1]->returned_at)->toBeNull()
        ->and($device->refresh()->current_employee_id)->toBe($john->id);
});
