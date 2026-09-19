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
        Schema::create('server_credentials', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('server_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->string('username');
            $table->text('password');

            $table->timestamps();

            $table->unique(['server_id', 'username']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_credentials');
    }
};
