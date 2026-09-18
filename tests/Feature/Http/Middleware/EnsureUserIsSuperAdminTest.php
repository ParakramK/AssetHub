<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(['web', 'auth', 'superadmin'])
        ->get('/_test/superadmin', fn () => response()->json(['ok' => true]))
        ->name('_test.superadmin');
});

test('redirects guests to the login page', function () {
    $this->get('/_test/superadmin')->assertRedirect('/login');
});

test('returns 403 for non-super-admin roles', function (string $roleName, bool $isSuperAdmin) {
    $user = User::factory()->make();
    $user->setRelation('role', Role::factory()->make([
        'name' => $roleName,
        'is_super_admin' => $isSuperAdmin,
    ]));

    $this->actingAs($user)->get('/_test/superadmin')->assertForbidden();
})->with([
    ['Admin', false],
    ['User', false],
]);

test('returns 403 when the user has no role', function () {
    $user = User::factory()->make();
    $user->setRelation('role', null);

    $this->actingAs($user)->get('/_test/superadmin')->assertForbidden();
});

test('allows super admins', function () {
    $user = User::factory()->make();
    $user->setRelation('role', Role::factory()->superAdmin()->make());

    $this->actingAs($user)->getJson('/_test/superadmin')->assertOk()->assertJson(['ok' => true]);
});
