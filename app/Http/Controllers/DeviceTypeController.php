<?php

namespace App\Http\Controllers;

use App\Models\DeviceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DeviceTypeController extends Controller
{
    public function index(Request $request): Response
    {
        // Filters are sanitized defensively (not validated): a validation
        // redirect on a GET filter URL could loop back onto the same URL.
        $search = Str::limit(trim((string) $request->query('search', '')), 255);

        $types = DeviceType::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();

        return Inertia::render('device-types/index', [
            'types' => $types,
            'filters' => ['search' => $search],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('device-types/create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:device_types,name'],
        ]);

        DeviceType::create($validated);

        return redirect()
            ->route('device-types.index')
            ->with('success', 'Device type created successfully.');
    }
}
