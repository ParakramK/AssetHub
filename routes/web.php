<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\ServerCredentialController;
use App\Http\Controllers\SshKeyController;
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
        ->middleware('permission:servers.update')
        ->name('servers.credentials.store');
    Route::delete('servers/{server}/credentials/{credential}', [ServerCredentialController::class, 'destroy'])
        ->middleware('permission:servers.update')
        ->name('servers.credentials.destroy');
    Route::post('servers/{server}/ssh-keys', [SshKeyController::class, 'store'])
        ->middleware('permission:servers.update')
        ->name('servers.ssh-keys.store');
    Route::delete('servers/{server}/ssh-keys/{key}', [SshKeyController::class, 'destroy'])
        ->middleware('permission:servers.update')
        ->name('servers.ssh-keys.destroy');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
