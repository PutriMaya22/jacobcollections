<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Update ENUM values to include the current allowed roles
        // Works for MySQL/MariaDB. SQLite will ignore as it doesn't support ENUM.
        try {
            DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('admin','user_input','user_operasional') NOT NULL DEFAULT 'user_input'");
        } catch (\Throwable $e) {
            // Ignore on databases that don't support ENUM or if column doesn't exist
        }
    }

    public function down(): void
    {
        // Optionally revert to previous set if needed (safe no-op)
        try {
            DB::statement("ALTER TABLE `users` MODIFY `role` ENUM('admin','user','user_operasional') NOT NULL DEFAULT 'user'");
        } catch (\Throwable $e) {
            // Ignore on databases that don't support ENUM or if column doesn't exist
        }
    }
};


