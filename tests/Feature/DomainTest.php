<?php

use App\Models\Company;
use App\Models\Domain;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Spatie\Permission\PermissionRegistrar;

beforeEach(fn () => app(PermissionRegistrar::class)->forgetCachedPermissions());

test('creates a domain with a uuid id linked to a company and creator', function () {
    $domain = Domain::factory()->create();

    expect($domain->id)->toBeString()
        ->and($domain->company)->toBeInstanceOf(Company::class)
        ->and($domain->creator)->not->toBeNull()
        ->and($domain->expiry_date)->toBeInstanceOf(Carbon::class);
});

test('expiry date is cast to a date without time', function () {
    $domain = Domain::factory()->create(['expiry_date' => '2027-05-20']);

    expect($domain->refresh()->expiry_date->format('Y-m-d'))->toBe('2027-05-20');
});

test('registrar and expiry date are optional', function () {
    $domain = Domain::factory()->create(['registrar' => null, 'expiry_date' => null]);

    expect($domain->refresh()->registrar)->toBeNull()
        ->and($domain->expiry_date)->toBeNull();
});

test('rejects a duplicate domain name', function () {
    Domain::factory()->create(['domain_name' => 'example.com']);

    Domain::factory()->create(['domain_name' => 'example.com']);
})->throws(QueryException::class);

test('prevents deleting a company that owns domains', function () {
    $domain = Domain::factory()->create();

    $domain->company->delete();
})->throws(QueryException::class);
