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
        Schema::create('ssh_keys', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('server_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('name');
            $table->text('public_key')->nullable();
            $table->text('private_key');

            $table->timestamps();

            $table->unique(['server_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ssh_keys');
    }
};
