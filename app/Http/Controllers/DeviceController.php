<?php

namespace App\Http\Controllers;

use App\Enums\DeviceStatus;
use App\Models\Company;
use App\Models\Device;
use App\Models\DeviceType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DeviceController extends Controller
{
    public function index(Request $request): Response
    {
        // Filters are sanitized defensively (not validated): a validation
        // redirect on a GET filter URL could loop back onto the same URL.
        $search = Str::limit(trim((string) $request->query('search', '')), 255);
        $companyId = $request->query('company_id');
        $companyId = is_string($companyId) && Str::isUuid($companyId) ? $companyId : null;
        $typeId = $request->query('type_id');
        $typeId = is_string($typeId) && Str::isUuid($typeId) ? $typeId : null;
        $status = is_string($request->query('status')) ? DeviceStatus::tryFrom($request->query('status')) : null;

        $devices = Device::query()
            ->with(['company', 'type', 'currentEmployee'])
            ->when($search !== '', fn ($query) => $query->where(
                fn ($query) => $query->where('code', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%")
                    ->orWhere('serial_no', 'like', "%{$search}%")
            ))
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->when($typeId, fn ($query) => $query->where('device_type_id', $typeId))
            ->when($status, fn ($query) => $query->where('status', $status->value))
            ->orderBy('code')
            ->get();

        return Inertia::render('devices/index', [
            'devices' => $devices,
            'filters' => [
                'search' => $search,
                'company_id' => $companyId,
                'type_id' => $typeId,
                'status' => $status?->value,
            ],
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'types' => DeviceType::query()->orderBy('name')->get(['id', 'name']),
            'statuses' => collect(DeviceStatus::cases())->map(fn (DeviceStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ])->all(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('devices/create', [
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'types' => DeviceType::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'uuid', 'exists:companies,id'],
            'device_type_id' => ['required', 'uuid', 'exists:device_types,id'],
            'brand' => ['required', 'string', 'max:255'],
            'model' => ['required', 'string', 'max:255'],
            'imei' => ['nullable', 'string', 'max:30', 'unique:devices,imei'],
            'mac_address' => ['nullable', 'string', 'max:30', 'unique:devices,mac_address'],
            'serial_no' => ['nullable', 'string', 'max:100', 'unique:devices,serial_no'],
        ]);

        // The asset code and default status are set by the model hook.
        Device::create($validated);

        return redirect()
            ->route('devices.index')
            ->with('success', 'Device created successfully.');
    }
}
