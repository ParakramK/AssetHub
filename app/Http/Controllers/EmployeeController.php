<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class EmployeeController extends Controller
{
    public function index(Request $request): Response
    {
        // Filters are sanitized defensively (not validated): a validation
        // redirect on a GET filter URL could loop back onto the same URL.
        $search = Str::limit(trim((string) $request->query('search', '')), 255);
        $companyId = $request->query('company_id');
        $companyId = is_string($companyId) && Str::isUuid($companyId) ? $companyId : null;

        $employees = Employee::query()
            ->with('company')
            ->when($search !== '', fn ($query) => $query->where(
                fn ($query) => $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile_no', 'like', "%{$search}%")
            ))
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->orderBy('name')
            ->get();

        return Inertia::render('employees/index', [
            'employees' => $employees,
            'filters' => [
                'search' => $search,
                'company_id' => $companyId,
            ],
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('employees/create', [
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'uuid', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:employees,email'],
            'mobile_no' => ['nullable', 'string', 'max:20'],
        ]);

        Employee::create($validated);

        return redirect()
            ->route('employees.index')
            ->with('success', 'Employee created successfully.');
    }
}
