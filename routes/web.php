<?php

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DomainController;
use App\Http\Controllers\RoleController;
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
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
