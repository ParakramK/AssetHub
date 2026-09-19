<?php

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\PermissionRegistrar;

beforeEach(fn () => app(PermissionRegistrar::class)->forgetCachedPermissions());

test('redirects guests to the login page', function (string $method, string $uri) {
    $this->{$method}($uri)->assertRedirect('/login');
})->with([
    ['get', '/employees'],
    ['get', '/employees/create'],
    ['post', '/employees'],
]);

test('returns 403 for non-super-admins', function (string $method, string $uri) {
    $this->actingAs(standardUser())->{$method}($uri)->assertForbidden();
})->with([
    ['get', '/employees'],
    ['get', '/employees/create'],
    ['post', '/employees'],
]);

test('renders the employees list for super admins', function () {
    $employee = Employee::factory()->create(['name' => 'Jane Doe']);

    $this->actingAs(superAdminUser())->get('/employees')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('employees/index')
            ->has('employees', 1)
            ->where('employees.0.name', 'Jane Doe')
            ->where('employees.0.company.name', $employee->company->name)
            ->where('filters.search', '')
            ->where('filters.company_id', null)
        );
});

test('renders the create page for super admins', function () {
    Company::factory()->create(['name' => 'Acme Corporation']);

    $this->actingAs(superAdminUser())->get('/employees/create')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('employees/create')
            ->has('companies', 1)
            ->where('companies.0.name', 'Acme Corporation')
        );
});

test('requires company, name, and email when storing an employee', function () {
    $this->actingAs(superAdminUser())->post('/employees', [
        'company_id' => '',
        'name' => '',
        'email' => 'not-an-email',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['company_id', 'name', 'email']);

    expect(Employee::count())->toBe(0);
});

test('rejects an unknown company and a duplicate email', function () {
    Employee::factory()->create(['email' => 'jane@example.com']);

    $this->actingAs(superAdminUser())->post('/employees', [
        'company_id' => (string) Str::uuid(),
        'name' => 'Jane',
        'email' => 'jane@example.com',
    ])
        ->assertRedirect()
        ->assertSessionHasErrors(['company_id', 'email']);

    expect(Employee::where('name', 'Jane')->exists())->toBeFalse();
});

test('stores an employee and redirects to the list', function () {
    $company = Company::factory()->create();

    $this->actingAs(superAdminUser())->post('/employees', [
        'company_id' => $company->id,
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'mobile_no' => '+977-9812345678',
    ])->assertRedirect('/employees');

    $employee = Employee::where('email', 'jane@example.com')->firstOrFail();
    expect($employee->company_id)->toBe($company->id)
        ->and($employee->name)->toBe('Jane Doe')
        ->and($employee->mobile_no)->toBe('+977-9812345678');
});

test('searches employees and filters by company', function () {
    $acme = Company::factory()->create(['name' => 'Acme']);
    $other = Company::factory()->create(['name' => 'Other']);
    Employee::factory()->create(['name' => 'Jane Doe', 'email' => 'jane@acme.test', 'company_id' => $acme->id]);
    Employee::factory()->create(['name' => 'John Smith', 'email' => 'john@other.test', 'company_id' => $other->id]);

    $this->actingAs(superAdminUser())->get('/employees?search=jane')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('employees', 1)
            ->where('employees.0.name', 'Jane Doe')
        );

    $this->actingAs(superAdminUser())->get("/employees?company_id={$other->id}")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->has('employees', 1)
            ->where('employees.0.name', 'John Smith')
        );
});
