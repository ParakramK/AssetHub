<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Ledger of every SIM-to-employee assignment, so the full usage history
     * of a SIM is preserved. An open row (returned_at null) means the SIM is
     * currently with that employee. History rows keep employees undeletable
     * (restrict) so the audit trail stays intact.
     */
    public function up(): void
    {
        Schema::create('sim_assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('sim_card_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignUuid('employee_id')
                ->constrained()
                ->restrictOnDelete();

            $table->timestamp('assigned_at');
            $table->timestamp('returned_at')->nullable();

            $table->timestamps();

            $table->index(['sim_card_id', 'assigned_at']);
            $table->index(['employee_id', 'assigned_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sim_assignments');
    }
};
