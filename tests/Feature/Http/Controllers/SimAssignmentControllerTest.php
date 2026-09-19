<?php

use App\Models\Employee;
use App\Models\SimAssignment;
use App\Models\SimCard;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

beforeEach(fn () => app(PermissionRegistrar::class)->forgetCachedPermissions());

test('redirects guests to the login page', function () {
    $employee = Employee::factory()->create();
    $assignment = SimAssignment::factory()->create();

    $this->get("/employees/{$employee->id}")->assertRedirect('/login');
    $this->post("/employees/{$employee->id}/sim-assignments", [])->assertRedirect('/login');
    $this->post("/sim-assignments/{$assignment->id}/return")->assertRedirect('/login');
});

test('returns 403 for non-super-admins', function () {
    $employee = Employee::factory()->create();
    $assignment = SimAssignment::factory()->create();

    $user = standardUser();
    $this->actingAs($user)->get("/employees/{$employee->id}")->assertForbidden();
    $this->actingAs($user)->post("/employees/{$employee->id}/sim-assignments", [])->assertForbidden();
    $this->actingAs($user)->post("/sim-assignments/{$assignment->id}/return")->assertForbidden();
});

test('renders the employee detail page with sims and history', function () {
    $employee = Employee::factory()->create(['name' => 'Jane Doe']);
    $sim = SimCard::factory()->create(['company_id' => $employee->company_id, 'number' => '+977-9812345678']);
    SimAssignment::factory()->create([
        'sim_card_id' => $sim->id,
        'employee_id' => $employee->id,
    ]);
    $sim->update(['current_employee_id' => $employee->id]);

    $this->actingAs(superAdminUser())->get("/employees/{$employee->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('employees/show')
            ->where('employee.name', 'Jane Doe')
            ->has('currentSims', 1)
            ->where('currentSims.0.number', '+977-9812345678')
            ->has('history', 1)
        );
});

test('assigns a sim and syncs holder and status', function () {
    $employee = Employee::factory()->create();
    $sim = SimCard::factory()->create(['company_id' => $employee->company_id, 'present_status' => 'returned_to_it']);

    $this->actingAs(superAdminUser())->post("/employees/{$employee->id}/sim-assignments", [
        'sim_card_id' => $sim->id,
    ])->assertRedirect("/employees/{$employee->id}");

    $sim->refresh();
    expect($sim->current_employee_id)->toBe($employee->id)
        ->and($sim->present_status->value)->toBe('assigned')
        ->and(SimAssignment::where('sim_card_id', $sim->id)->whereNull('returned_at')->count())->toBe(1);
});

test('rejects assigning an already assigned sim', function () {
    $employee = Employee::factory()->create();
    $other = Employee::factory()->create(['company_id' => $employee->company_id]);
    $sim = SimCard::factory()->create(['company_id' => $employee->company_id, 'current_employee_id' => $other->id]);

    $this->actingAs(superAdminUser())->post("/employees/{$employee->id}/sim-assignments", [
        'sim_card_id' => $sim->id,
    ])
        ->assertRedirect()
        ->assertSessionHasErrors('sim_card_id');

    expect(SimAssignment::where('sim_card_id', $sim->id)->count())->toBe(0);
});

test('rejects assigning a sim from another company', function () {
    $employee = Employee::factory()->create();
    $sim = SimCard::factory()->create();

    $this->actingAs(superAdminUser())->post("/employees/{$employee->id}/sim-assignments", [
        'sim_card_id' => $sim->id,
    ])
        ->assertRedirect()
        ->assertSessionHasErrors('sim_card_id');

    expect(SimAssignment::where('sim_card_id', $sim->id)->count())->toBe(0);
});

test('rejects an unknown sim', function () {
    $employee = Employee::factory()->create();

    $this->actingAs(superAdminUser())->post("/employees/{$employee->id}/sim-assignments", [
        'sim_card_id' => (string) Str::uuid(),
    ])
        ->assertRedirect()
        ->assertSessionHasErrors('sim_card_id');
});

test('returns a sim and keeps the history row', function () {
    $employee = Employee::factory()->create();
    $sim = SimCard::factory()->create(['company_id' => $employee->company_id]);
    $assignment = SimAssignment::factory()->create([
        'sim_card_id' => $sim->id,
        'employee_id' => $employee->id,
    ]);
    $sim->update(['current_employee_id' => $employee->id, 'present_status' => 'assigned']);

    $this->actingAs(superAdminUser())->post("/sim-assignments/{$assignment->id}/return")
        ->assertRedirect();

    $sim->refresh();
    expect($assignment->refresh()->returned_at)->not->toBeNull()
        ->and($sim->current_employee_id)->toBeNull()
        ->and($sim->present_status->value)->toBe('returned_to_it')
        ->and(SimAssignment::where('sim_card_id', $sim->id)->count())->toBe(1);
});

test('rejects returning an already closed assignment', function () {
    $assignment = SimAssignment::factory()->returned()->create();

    $this->actingAs(superAdminUser())->post("/sim-assignments/{$assignment->id}/return")
        ->assertStatus(422);
});

test('maintains full history across reassignments', function () {
    $jane = Employee::factory()->create();
    $john = Employee::factory()->create(['company_id' => $jane->company_id]);
    $sim = SimCard::factory()->create(['company_id' => $jane->company_id]);

    $this->actingAs(superAdminUser())->post("/employees/{$jane->id}/sim-assignments", ['sim_card_id' => $sim->id])
        ->assertRedirect();
    $first = SimAssignment::where('sim_card_id', $sim->id)->whereNull('returned_at')->firstOrFail();

    $this->actingAs(superAdminUser())->post("/sim-assignments/{$first->id}/return")->assertRedirect();

    $this->actingAs(superAdminUser())->post("/employees/{$john->id}/sim-assignments", ['sim_card_id' => $sim->id])
        ->assertRedirect();

    $history = SimAssignment::where('sim_card_id', $sim->id)->orderBy('assigned_at')->get();
    expect($history)->toHaveCount(2)
        ->and($history[0]->employee_id)->toBe($jane->id)
        ->and($history[0]->returned_at)->not->toBeNull()
        ->and($history[1]->employee_id)->toBe($john->id)
        ->and($history[1]->returned_at)->toBeNull()
        ->and($sim->refresh()->current_employee_id)->toBe($john->id);
});
