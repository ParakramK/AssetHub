<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(): Response
    {
        $users = User::query()
            ->with('roles')
            ->withCount(['createdServers', 'createdDomains'])
            ->orderBy('name')
            ->get();

        return Inertia::render('users/index', [
            'users' => $users,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('users/create', [
            'roles' => Role::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'roles' => ['array'],
            'roles.*' => ['uuid', 'exists:roles,id'],
        ]);

        // The 'hashed' cast on the model hashes the plain password and leaves
        // already-hashed values untouched, so the raw value is stored as-is.
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
        ]);

        $user->syncRoles(Role::whereKey($validated['roles'] ?? [])->get());

        return redirect()
            ->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 403, 'You cannot delete your own account.');

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
