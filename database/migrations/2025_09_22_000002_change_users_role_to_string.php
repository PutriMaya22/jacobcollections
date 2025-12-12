<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // MySQL cannot modify ENUM -> string with the schema builder directly; use raw SQL
        try {
            // Preserve existing values; set default to 'user_input'
            DB::statement("ALTER TABLE `users` MODIFY `role` VARCHAR(50) NOT NULL DEFAULT 'user_input'");
        } catch (\Throwable $e) {
            // Fallback for drivers that support change() (e.g., SQLite during tests)
            if (Schema::hasColumn('users', 'role')) {
                try {
                    Schema::table('users', function (Blueprint $table) {
                        $table->string('role', 50)->default('user_input')->change();
                    });
                } catch (\Throwable $ignored) {
                }
            }
        }
    }

    public function down(): void
    {
        // Best-effort revert to ENUM; if it fails, keep VARCHAR
        try {
            DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('admin','user_input','user_operasional') NOT NULL DEFAULT 'user_input'");
        } catch (\Throwable $e) {
            // no-op
        }
    }
};


