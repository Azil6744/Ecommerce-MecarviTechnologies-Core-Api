<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::connection($this->getConnection())->getConnection()->getDriverName();

        // For SQLite, we need to recreate the table with the new enum values
        if ($driver === 'sqlite') {
            // SQLite doesn't support ALTER TABLE for enum, so we need to recreate
            Schema::table('users', function (Blueprint $table) {
                // Drop the old role column
                $table->dropColumn('role');
            });
            
            Schema::table('users', function (Blueprint $table) {
                // Add new role column with customer option
                $table->enum('role', ['super_admin', 'editor', 'viewer', 'customer'])->default('viewer')->after('password');
            });
        } elseif ($driver === 'pgsql') {
            // PostgreSQL: avoid enum()->change() SQL generation; manage check constraint explicitly.
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            DB::statement("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(255)");
            DB::statement("UPDATE users SET role = 'viewer' WHERE role NOT IN ('super_admin','editor','viewer','customer') OR role IS NULL");
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'viewer'");
            DB::statement("ALTER TABLE users ALTER COLUMN role SET NOT NULL");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('super_admin','editor','viewer','customer'))");
        } else {
            // For other databases, modify the enum
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['super_admin', 'editor', 'viewer', 'customer'])->default('viewer')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::connection($this->getConnection())->getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
            
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['super_admin', 'editor', 'viewer'])->default('viewer')->after('password');
            });
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            DB::statement("UPDATE users SET role = 'viewer' WHERE role NOT IN ('super_admin','editor','viewer') OR role IS NULL");
            DB::statement("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(255)");
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'viewer'");
            DB::statement("ALTER TABLE users ALTER COLUMN role SET NOT NULL");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('super_admin','editor','viewer'))");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['super_admin', 'editor', 'viewer'])->default('viewer')->change();
            });
        }
    }
};
