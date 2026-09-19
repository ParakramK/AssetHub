<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Device;
use App\Models\Employee;
use App\Models\SimCard;
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

    public function show(Employee $employee): Response
    {
        $employee->load('company');

        return Inertia::render('employees/show', [
            'employee' => $employee,
            'currentSims' => $employee->currentSimCards()->orderBy('number')->get()->map(fn ($sim) => [
                'id' => $sim->id,
                'number' => $sim->number,
                'provider' => $sim->provider,
                'assignment_id' => $sim->assignments()->whereNull('returned_at')->latest('assigned_at')->first()?->id,
            ])->all(),
            'history' => $employee->simAssignments()
                ->with('simCard:id,number')
                ->orderByDesc('assigned_at')
                ->get(),
            'availableSims' => SimCard::where('company_id', $employee->company_id)
                ->whereNull('current_employee_id')
                ->orderBy('number')
                ->get(['id', 'number', 'provider']),
            'currentDevices' => $employee->currentDevices()->orderBy('code')->get()->map(fn ($device) => [
                'id' => $device->id,
                'code' => $device->code,
                'brand' => $device->brand,
                'model' => $device->model,
                'assignment_id' => $device->assignments()->whereNull('returned_at')->latest('assigned_at')->first()?->id,
            ])->all(),
            'deviceHistory' => $employee->deviceAssignments()
                ->with('device:id,code')
                ->orderByDesc('assigned_at')
                ->get(),
            'availableDevices' => Device::where('company_id', $employee->company_id)
                ->whereNull('current_employee_id')
                ->orderBy('code')
                ->get(['id', 'code', 'brand', 'model']),
        ]);
    }
}
