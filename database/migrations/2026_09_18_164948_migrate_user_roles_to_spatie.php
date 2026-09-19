<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Moves the legacy single-role assignment (`users.role_id`) into Spatie's
     * `model_has_roles` pivot, then removes the column so the pivot is the
     * single source of truth for role assignment.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNotNull('role_id')
            ->select(['id', 'role_id'])
            ->orderBy('id')
            ->chunk(500, function ($users) {
                DB::table('model_has_roles')->upsert(
                    $users->map(fn ($user) => [
                        'role_id' => $user->role_id,
                        'model_type' => 'App\\Models\\User',
                        'model_id' => $user->id,
                    ])->all(),
                    ['role_id', 'model_type', 'model_id'],
                );
            });

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['role_id']);
            $table->dropColumn('role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignUuid('role_id')
                ->nullable()
                ->constrained('roles')
                ->restrictOnDelete();
        });
    }
};
