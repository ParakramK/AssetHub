<?php

namespace App\Http\Controllers;

use App\Enums\SimStatus;
use App\Models\Employee;
use App\Models\SimAssignment;
use App\Models\SimCard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SimAssignmentController extends Controller
{
    public function store(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'sim_card_id' => ['required', 'uuid', 'exists:sim_cards,id'],
        ]);

        $simCard = SimCard::findOrFail($validated['sim_card_id']);

        if ($simCard->company_id !== $employee->company_id) {
            return back()
                ->withErrors(['sim_card_id' => 'The SIM card belongs to a different company.'])
                ->withInput();
        }

        if ($simCard->current_employee_id !== null) {
            return back()
                ->withErrors(['sim_card_id' => 'The SIM card is already assigned.'])
                ->withInput();
        }

        DB::transaction(function () use ($simCard, $employee) {
            // Close any stale open rows; current_employee_id is the guard.
            SimAssignment::where('sim_card_id', $simCard->id)
                ->whereNull('returned_at')
                ->update(['returned_at' => now()]);

            SimAssignment::create([
                'sim_card_id' => $simCard->id,
                'employee_id' => $employee->id,
                'assigned_at' => now(),
            ]);

            $simCard->update([
                'current_employee_id' => $employee->id,
                'present_status' => SimStatus::Assigned,
            ]);
        });

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'SIM card assigned successfully.');
    }

    public function return(SimAssignment $assignment): RedirectResponse
    {
        abort_if($assignment->returned_at !== null, 422, 'The assignment is already closed.');

        DB::transaction(function () use ($assignment) {
            $assignment->update(['returned_at' => now()]);

            $simCard = $assignment->simCard;

            if (! $simCard->assignments()->whereNull('returned_at')->exists()) {
                $simCard->update([
                    'current_employee_id' => null,
                    'present_status' => SimStatus::ReturnedToIt,
                ]);
            }
        });

        return back()->with('success', 'SIM card returned successfully.');
    }
}
