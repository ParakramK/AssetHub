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
     * Renames servers.user_id to created_by (nullable, nullOnDelete, matching
     * domains.created_by) and adds a human-friendly name. A rename-via-copy
     * is used instead of renameColumn so existing rows survive on every
     * database driver.
     */
    public function up(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->string('name')->after('company_id');
            $table->foreignUuid('created_by')
                ->nullable()
                ->after('user_id')
                ->constrained('users')
                ->nullOnDelete();
        });

        DB::table('servers')
            ->whereNull('created_by')
            ->whereNotNull('user_id')
            ->update(['created_by' => DB::raw('user_id')]);

        Schema::table('servers', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->foreignUuid('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
        });

        DB::table('servers')
            ->whereNull('user_id')
            ->whereNotNull('created_by')
            ->update(['user_id' => DB::raw('created_by')]);

        Schema::table('servers', function (Blueprint $table) {
            $table->dropForeign(['created_by']);
            $table->dropColumn('created_by');
            $table->dropColumn('name');
        });
    }
};
