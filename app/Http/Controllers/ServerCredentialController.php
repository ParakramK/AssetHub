<?php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServerCredentialController extends Controller
{
    public function store(Request $request, Server $server): RedirectResponse
    {
        $validated = $request->validate([
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('server_credentials')->where('server_id', $server->id),
            ],
            'password' => ['required', 'string'],
        ]);

        $server->credentials()->create($validated);

        return redirect()
            ->route('servers.show', $server)
            ->with('success', 'Credential added successfully.');
    }

    public function destroy(Server $server, string $credential): RedirectResponse
    {
        $server->credentials()->where('id', $credential)->firstOrFail()->delete();

        return redirect()
            ->route('servers.show', $server)
            ->with('success', 'Credential removed successfully.');
    }
}
