<?php

use App\Http\Controllers\CompanyController;
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

});
Route::middleware(['auth', 'superadmin'])->group(function () {
    Route::resource('roles', RoleController::class)->only(['index', 'create', 'store']);
    Route::resource('companies', CompanyController::class)->only(['index', 'create', 'store']);
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
