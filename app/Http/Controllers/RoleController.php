<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoleController extends Controller
{
    public function index(): Response
    {
        $roles = Role::query()->orderBy('name')->get();

        return Inertia::render('roles/index', [
            'roles' => $roles,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('roles/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_super_admin' => ['sometimes', 'boolean'],
        ]);

        Role::create([
            ...$validated,
            'guard_name' => 'web',
        ]);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role created successfully.');
    }
}
