<?php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SshKeyController extends Controller
{
    public function store(Request $request, Server $server): RedirectResponse
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('ssh_keys')->where('server_id', $server->id),
            ],
            'public_key' => ['nullable', 'string'],
            'private_key' => ['required', 'string'],
        ]);

        $server->sshKeys()->create($validated);

        return redirect()
            ->route('servers.show', $server)
            ->with('success', 'SSH key added successfully.');
    }

    public function destroy(Server $server, string $key): RedirectResponse
    {
        $server->sshKeys()->where('id', $key)->firstOrFail()->delete();

        return redirect()
            ->route('servers.show', $server)
            ->with('success', 'SSH key removed successfully.');
    }
}
