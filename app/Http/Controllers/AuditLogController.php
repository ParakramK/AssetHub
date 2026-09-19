<?php

namespace App\Http\Controllers;

use App\Enums\AuditAction;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        // Filters are sanitized defensively (not validated): a validation
        // redirect on a GET filter URL could loop back onto the same URL.
        $search = Str::limit(trim((string) $request->query('search', '')), 255);
        $action = $request->query('action');
        $action = is_string($action) && in_array($action, AuditAction::values(), true) ? $action : null;
        $model = Str::limit(trim((string) $request->query('model', '')), 255);

        $logs = AuditLog::query()
            ->with('actor:id,name,email')
            ->when($search !== '', fn ($query) => $query->where(
                fn ($query) => $query->where('auditable_id', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('auditable_type', 'like', "%{$search}%")
            ))
            ->when($action, fn ($query) => $query->where('action', $action))
            ->when($model !== '', fn ($query) => $query->where('auditable_type', 'like', "%{$model}%"))
            ->latest()
            ->limit(500)
            ->get();

        return Inertia::render('audit-logs/index', [
            'logs' => $logs,
            'filters' => [
                'search' => $search,
                'action' => $action,
                'model' => $model,
            ],
            'actions' => AuditAction::values(),
        ]);
    }

    public function destroy(AuditLog $auditLog): RedirectResponse
    {
        $auditLog->delete();

        return redirect()
            ->route('audit-logs.index')
            ->with('success', 'Audit log entry deleted successfully.');
    }

    public function prune(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'days' => ['required', 'integer', 'min:1', 'max:3650'],
        ]);

        $deleted = AuditLog::query()
            ->where('created_at', '<', now()->subDays($validated['days']))
            ->delete();

        return redirect()
            ->route('audit-logs.index')
            ->with('success', "{$deleted} audit log entries pruned successfully.");
    }
}
