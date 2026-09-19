<?php

namespace App\Http\Controllers;

use App\Enums\DeviceStatus;
use App\Models\Device;
use App\Models\DeviceAssignment;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DeviceAssignmentController extends Controller
{
    public function store(Request $request, Employee $employee): RedirectResponse
    {
        $validated = $request->validate([
            'device_id' => ['required', 'uuid', 'exists:devices,id'],
        ]);

        $device = Device::findOrFail($validated['device_id']);

        if ($device->company_id !== $employee->company_id) {
            return back()
                ->withErrors(['device_id' => 'The device belongs to a different company.'])
                ->withInput();
        }

        if ($device->current_employee_id !== null) {
            return back()
                ->withErrors(['device_id' => 'The device is already assigned.'])
                ->withInput();
        }

        DB::transaction(function () use ($device, $employee) {
            // Close any stale open rows; current_employee_id is the guard.
            DeviceAssignment::where('device_id', $device->id)
                ->whereNull('returned_at')
                ->update(['returned_at' => now()]);

            DeviceAssignment::create([
                'device_id' => $device->id,
                'employee_id' => $employee->id,
                'assigned_at' => now(),
            ]);

            $device->update([
                'current_employee_id' => $employee->id,
                'status' => DeviceStatus::Assigned,
            ]);
        });

        return redirect()
            ->route('employees.show', $employee)
            ->with('success', 'Device assigned successfully.');
    }

    public function return(DeviceAssignment $assignment): RedirectResponse
    {
        abort_if($assignment->returned_at !== null, 422, 'The assignment is already closed.');

        DB::transaction(function () use ($assignment) {
            $assignment->update(['returned_at' => now()]);

            $device = $assignment->device;

            if (! $device->assignments()->whereNull('returned_at')->exists()) {
                $device->update([
                    'current_employee_id' => null,
                    'status' => DeviceStatus::Available,
                ]);
            }
        });

        return back()->with('success', 'Device returned successfully.');
    }
}
