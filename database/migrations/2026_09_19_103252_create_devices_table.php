<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('devices', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('company_id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignUuid('device_type_id')
                ->constrained()
                ->restrictOnDelete();
            $table->foreignUuid('current_employee_id')
                ->nullable()
                ->constrained('employees')
                ->nullOnDelete();

            $table->string('brand');
            $table->string('model');
            $table->string('code', 20)->unique();
            $table->string('imei', 30)->nullable()->unique();
            $table->string('mac_address', 30)->nullable()->unique();
            $table->string('serial_no', 100)->nullable()->unique();
            $table->string('status', 20);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
