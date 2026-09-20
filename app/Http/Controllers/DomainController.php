<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Domain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DomainController extends Controller
{
    public function index(Request $request): Response
    {

        $search = Str::limit(trim((string) $request->query('search', '')), 255);
        $companyId = $request->query('company_id');
        $companyId = is_string($companyId) && Str::isUuid($companyId) ? $companyId : null;

        $domains = Domain::query()
            ->with('company')
            ->when($search !== '', fn ($query) => $query->where(
                fn ($query) => $query->where('domain_name', 'like', "%{$search}%")
                    ->orWhere('registrar', 'like', "%{$search}%")
            ))
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->orderBy('domain_name')
            ->get();

        return Inertia::render('domains/index', [
            'domains' => $domains,
            'filters' => [
                'search' => $search,
                'company_id' => $companyId,
            ],
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('domains/create', [
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'uuid', 'exists:companies,id'],
            'domain_name' => ['required', 'string', 'max:255', 'unique:domains,domain_name'],
            'registrar' => ['nullable', 'string', 'max:255'],
            'expiry_date' => ['nullable', 'date'],
        ]);

        Domain::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('domains.index')
            ->with('success', 'Domain created successfully.');
    }

    public function edit(Domain $domain): Response
    {
        return Inertia::render('domains/edit', [
            'domain' => [
                ...$domain->only(['id', 'company_id', 'domain_name', 'registrar']),
                'expiry_date' => $domain->expiry_date?->format('Y-m-d'),
            ],
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Domain $domain): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'uuid', 'exists:companies,id'],
            'domain_name' => ['required', 'string', 'max:255', Rule::unique('domains', 'domain_name')->ignore($domain->getKey())],
            'registrar' => ['nullable', 'string', 'max:255'],
            'expiry_date' => ['nullable', 'date'],
        ]);

        $domain->update($validated);

        return redirect()
            ->route('domains.index')
            ->with('success', 'Domain updated successfully.');
    }
}
