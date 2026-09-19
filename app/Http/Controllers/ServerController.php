<?php

namespace App\Http\Controllers;

use App\Enums\ServerType;
use App\Models\Company;
use App\Models\Server;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ServerController extends Controller
{
    public function index(Request $request): Response
    {
        // Filters are sanitized defensively (not validated): a validation
        // redirect on a GET filter URL could loop back onto the same URL.
        $search = Str::limit(trim((string) $request->query('search', '')), 255);
        $companyId = $request->query('company_id');
        $companyId = is_string($companyId) && Str::isUuid($companyId) ? $companyId : null;
        $type = is_string($request->query('type')) ? ServerType::tryFrom($request->query('type')) : null;

        $servers = Server::query()
            ->with(['company', 'creator'])
            ->when($search !== '', fn ($query) => $query->where(
                fn ($query) => $query->where('name', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
            ))
            ->when($companyId, fn ($query) => $query->where('company_id', $companyId))
            ->when($type, fn ($query) => $query->where('type', $type->value))
            ->orderBy('name')
            ->get();

        return Inertia::render('servers/index', [
            'servers' => $servers,
            'filters' => [
                'search' => $search,
                'company_id' => $companyId,
                'type' => $type?->value,
            ],
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'types' => collect(ServerType::cases())->map(fn (ServerType $type) => [
                'value' => $type->value,
                'label' => $type->name,
            ])->all(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('servers/create', [
            'companies' => Company::query()->orderBy('name')->get(['id', 'name']),
            'types' => collect(ServerType::cases())->map(fn (ServerType $type) => [
                'value' => $type->value,
                'label' => $type->name,
                'defaultPort' => $type->defaultPort(),
            ])->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_id' => ['required', 'uuid', 'exists:companies,id'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(ServerType::class)],
            'ip_address' => ['required', 'string', 'max:255'],
            'port' => ['nullable', 'integer', 'min:1', 'max:65535'],
        ]);

        Server::create([
            ...$validated,
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('servers.index')
            ->with('success', 'Server created successfully.');
    }

    public function show(Request $request, Server $server): Response
    {
        $server->load(['company', 'creator']);

        return Inertia::render('servers/show', [
            'server' => $server,
            // Secrets are never sent to the client: select safe columns only
            // ($hidden on the models is the second layer of defense).
            // Public keys are not secret and are shown inline.
            'credentials' => $server->credentials()->orderBy('username')->get(['id', 'username', 'created_at']),
            'sshKeys' => $server->sshKeys()->orderBy('name')->get(['id', 'name', 'public_key', 'created_at']),
            'can' => [
                'viewCredentials' => $request->user()->can('credentials.view'),
                'createCredentials' => $request->user()->can('credentials.create'),
                'deleteCredentials' => $request->user()->can('credentials.delete'),
                'viewSshKeys' => $request->user()->can('ssh-keys.view'),
                'createSshKeys' => $request->user()->can('ssh-keys.create'),
                'deleteSshKeys' => $request->user()->can('ssh-keys.delete'),
            ],
        ]);
    }
}
