<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\ServerCredentialController;
use App\Http\Controllers\SshKeyController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');

    Route::get('roles', [RoleController::class, 'index'])
        ->middleware('permission:roles.view')
        ->name('roles.index');
    Route::get('roles/create', [RoleController::class, 'create'])
        ->middleware('permission:roles.create')
        ->name('roles.create');
    Route::post('roles', [RoleController::class, 'store'])
        ->middleware('permission:roles.create')
        ->name('roles.store');
    Route::get('roles/{role}/edit', [RoleController::class, 'edit'])
        ->middleware('permission:roles.update')
        ->name('roles.edit');
    Route::put('roles/{role}', [RoleController::class, 'update'])
        ->middleware('permission:roles.update')
        ->name('roles.update');

    Route::get('users', [UserController::class, 'index'])
        ->middleware('permission:users.view')
        ->name('users.index');
    Route::get('users/create', [UserController::class, 'create'])
        ->middleware('permission:users.create')
        ->name('users.create');
    Route::post('users', [UserController::class, 'store'])
        ->middleware('permission:users.create')
        ->name('users.store');
    Route::delete('users/{user}', [UserController::class, 'destroy'])
        ->middleware('permission:users.delete')
        ->name('users.destroy');

    Route::get('companies', [CompanyController::class, 'index'])
        ->middleware('permission:companies.view')
        ->name('companies.index');
    Route::get('companies/create', [CompanyController::class, 'create'])
        ->middleware('permission:companies.create')
        ->name('companies.create');
    Route::post('companies', [CompanyController::class, 'store'])
        ->middleware('permission:companies.create')
        ->name('companies.store');

    Route::get('domains', [DomainController::class, 'index'])
        ->middleware('permission:domains.view')
        ->name('domains.index');
    Route::get('domains/create', [DomainController::class, 'create'])
        ->middleware('permission:domains.create')
        ->name('domains.create');
    Route::post('domains', [DomainController::class, 'store'])
        ->middleware('permission:domains.create')
        ->name('domains.store');

    Route::get('employees', [EmployeeController::class, 'index'])
        ->middleware('permission:employees.view')
        ->name('employees.index');
    Route::get('employees/create', [EmployeeController::class, 'create'])
        ->middleware('permission:employees.create')
        ->name('employees.create');
    Route::post('employees', [EmployeeController::class, 'store'])
        ->middleware('permission:employees.create')
        ->name('employees.store');

    Route::get('servers', [ServerController::class, 'index'])
        ->middleware('permission:servers.view')
        ->name('servers.index');
    Route::get('servers/create', [ServerController::class, 'create'])
        ->middleware('permission:servers.create')
        ->name('servers.create');
    Route::post('servers', [ServerController::class, 'store'])
        ->middleware('permission:servers.create')
        ->name('servers.store');
    Route::get('servers/{server}', [ServerController::class, 'show'])
        ->middleware('permission:servers.view')
        ->name('servers.show');
    Route::post('servers/{server}/credentials', [ServerCredentialController::class, 'store'])
        ->middleware('permission:credentials.create')
        ->name('servers.credentials.store');
    Route::delete('servers/{server}/credentials/{credential}', [ServerCredentialController::class, 'destroy'])
        ->middleware('permission:credentials.delete')
        ->name('servers.credentials.destroy');
    Route::get('servers/{server}/credentials/{credential}/reveal', [ServerCredentialController::class, 'reveal'])
        ->middleware('permission:credentials.view')
        ->name('servers.credentials.reveal');
    Route::post('servers/{server}/ssh-keys', [SshKeyController::class, 'store'])
        ->middleware('permission:ssh-keys.create')
        ->name('servers.ssh-keys.store');
    Route::delete('servers/{server}/ssh-keys/{key}', [SshKeyController::class, 'destroy'])
        ->middleware('permission:ssh-keys.delete')
        ->name('servers.ssh-keys.destroy');
    Route::get('servers/{server}/ssh-keys/{key}/reveal', [SshKeyController::class, 'reveal'])
        ->middleware('permission:ssh-keys.view')
        ->name('servers.ssh-keys.reveal');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
