<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Spatie scopes every role query by `guard_name`. This adds the column to
     * the existing roles table without touching existing records: the default
     * backfills every current row.
     */
    public function up(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->string('guard_name')->default('web')->after('name');
            $table->unique(['name', 'guard_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique(['name', 'guard_name']);
            $table->dropColumn('guard_name');
        });
    }
};
