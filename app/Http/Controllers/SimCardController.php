<?php

namespace App\Http\Controllers;

use App\Enums\SimProvider;
use App\Enums\SimStatus;
use App\Models\Company;
use App\Models\SimCard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SimCardController extends Controller
{
    public function index(Request $request): Response
    {
        // Filters are sanitized defensively (not validated): a validation
        // redirect on a GET filter URL could loop back onto the same URL.
        $search = Str::limit(trim((string) $request->query('search', '')), 255);
        $companyId = $request->query('company_id');
        $companyId = is_string($companyId) && Str::isUuid($companyId) ? $companyId : null;
        $provider = is_string($request->query('provider')) ? SimProvider::tryFrom($request->query('provider')) : null;
        $status = is_string($request->query('status')) ? SimStatus::tryFrom($request->query('status')) : null;

        $simCards = SimCard::query()
            ->with(['company', 'currentEmployee'])
            ->when($search !== '', fn ($query) => $query->where('number', 'like', "%{$search}%"))
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->when($provider, fn ($query) => $query->where('provider', $provider->value))
            ->when($status, fn ($query) => $query->where('present_status', $status->value))
            ->orderBy('number')
            ->get();

        return Inertia::render('sim-cards/index', [
            'simCards' => $simCards,
            'filters' => [
                'search' => $search,
                'company_id' => $companyId,
                'provider' => $provider?->value,
                'status' => $status?->value,
            ],
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'providers' => collect(SimProvider::cases())->map(fn (SimProvider $provider) => [
                'value' => $provider->value,
                'label' => $provider->name,
            ])->all(),
            'statuses' => collect(SimStatus::cases())->map(fn (SimStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ])->all(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('sim-cards/create', [
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'providers' => collect(SimProvider::cases())->map(fn (SimProvider $provider) => [
                'value' => $provider->value,
                'label' => $provider->name,
            ])->all(),
            'statuses' => collect(SimStatus::cases())->map(fn (SimStatus $status) => [
                'value' => $status->value,
                'label' => $status->label(),
            ])->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'uuid', 'exists:companies,id'],
            'provider' => ['required', Rule::enum(SimProvider::class)],
            'number' => ['required', 'string', 'max:20', 'unique:sim_cards,number'],
            'present_status' => ['required', Rule::enum(SimStatus::class)],
        ]);

        SimCard::create($validated);

        return redirect()
            ->route('sim-cards.index')
            ->with('success', 'SIM card created successfully.');
    }
}
