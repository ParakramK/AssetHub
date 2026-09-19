<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Denormalized pointer to the SIM's current holder for fast reads. The
     * sim_assignments ledger remains the source of truth for history; this
     * column is maintained by the assignment flow (assign sets it, return
     * clears it). Nullable with nullOnDelete: deleting an employee must
     * unassign their SIMs, never delete them.
     */
    public function up(): void
    {
        Schema::table('sim_cards', function (Blueprint $table) {
            $table->foreignUuid('current_employee_id')
                ->nullable()
                ->after('company_id')
                ->constrained('employees')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sim_cards', function (Blueprint $table) {
            $table->dropForeign(['current_employee_id']);
            $table->dropColumn('current_employee_id');
        });
    }
};
