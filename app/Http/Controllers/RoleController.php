<?php

namespace App\Http\Controllers;

use App\Enums\PermissionName;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

    public function edit(Role $role): Response
    {
        return Inertia::render('roles/edit', [
            'role' => $role->only(['id', 'name', 'description', 'is_super_admin']),
            'modules' => PermissionName::grouped(),
            'rolePermissions' => $role->permissions()->pluck('name')->all(),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'permissions' => ['array'],
            'permissions.*' => [Rule::enum(PermissionName::class)],
        ]);

        // Every validated value is a legitimate app permission, so ensure its
        // row exists. This keeps assignment working for newly added enum cases
        // even if the seeder hasn't been re-run yet.
        $permissions = collect($validated['permissions'] ?? [])
            ->map(fn (string $name) => Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']))
            ->all();

        $role->syncPermissions($permissions);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role permissions updated successfully.');
    }
}
