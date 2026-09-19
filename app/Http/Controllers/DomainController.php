<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Domain;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DomainController extends Controller
{
    public function index(): Response
    {
        $domains = Domain::query()
            ->with('company')
            ->orderBy('domain_name')
            ->get();

        return Inertia::render('domains/index', [
            'domains' => $domains,
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
}
