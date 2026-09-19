<?php

namespace App\Http\Controllers;

use App\Enums\ServerType;
use App\Models\Company;
use App\Models\Server;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ServerController extends Controller
{
    public function index(): Response
    {
        $servers = Server::query()
            ->with(['company', 'creator'])
            ->orderBy('name')
            ->get();

        return Inertia::render('servers/index', [
            'servers' => $servers,
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

    public function show(Server $server): Response
    {
        $server->load(['company', 'creator']);

        return Inertia::render('servers/show', [
            'server' => $server,
            // Secrets are never sent to the client: select safe columns only
            // ($hidden on the models is the second layer of defense).
            'credentials' => $server->credentials()->orderBy('username')->get(['id', 'username', 'created_at']),
            'sshKeys' => $server->sshKeys()->orderBy('name')->get(['id', 'name', 'created_at']),
        ]);
    }
}
