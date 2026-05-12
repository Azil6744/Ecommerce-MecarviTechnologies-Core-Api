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

        if ($driver === 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });

            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['super_admin', 'admin', 'editor', 'viewer', 'customer', 'seller'])
                    ->default('viewer')
                    ->after('password');
            });
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            DB::statement("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(255)");
            DB::statement("UPDATE users SET role = 'viewer' WHERE role NOT IN ('super_admin','admin','editor','viewer','customer','seller') OR role IS NULL");
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'viewer'");
            DB::statement("ALTER TABLE users ALTER COLUMN role SET NOT NULL");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('super_admin','admin','editor','viewer','customer','seller'))");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['super_admin', 'admin', 'editor', 'viewer', 'customer', 'seller'])
                    ->default('viewer')
                    ->change();
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
                $table->enum('role', ['super_admin', 'admin', 'editor', 'viewer', 'customer'])
                    ->default('viewer')
                    ->after('password');
            });
        } elseif ($driver === 'pgsql') {
            DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_role_check");
            DB::statement("ALTER TABLE users ALTER COLUMN role TYPE VARCHAR(255)");
            DB::statement("UPDATE users SET role = 'viewer' WHERE role NOT IN ('super_admin','admin','editor','viewer','customer') OR role IS NULL");
            DB::statement("ALTER TABLE users ALTER COLUMN role SET DEFAULT 'viewer'");
            DB::statement("ALTER TABLE users ALTER COLUMN role SET NOT NULL");
            DB::statement("ALTER TABLE users ADD CONSTRAINT users_role_check CHECK (role IN ('super_admin','admin','editor','viewer','customer'))");
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['super_admin', 'admin', 'editor', 'viewer', 'customer'])
                    ->default('viewer')
                    ->change();
            });
        }
    }
};
