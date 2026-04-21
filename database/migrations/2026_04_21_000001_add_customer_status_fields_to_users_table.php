<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'banned_at')) {
                $table->timestamp('banned_at')->nullable()->after('email_verified_at');
            }

            if (!Schema::hasColumn('users', 'deactivated_at')) {
                $table->timestamp('deactivated_at')->nullable()->after('banned_at');
            }

            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('deactivated_at');
            }
        });

        if (Schema::hasColumn('users', 'role') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin', 'admin', 'editor', 'viewer', 'customer') NOT NULL DEFAULT 'viewer'");
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'role') && DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY role ENUM('super_admin', 'editor', 'viewer') NOT NULL DEFAULT 'viewer'");
        }

        Schema::table('users', function (Blueprint $table) {
            $columns = array_filter([
                Schema::hasColumn('users', 'banned_at') ? 'banned_at' : null,
                Schema::hasColumn('users', 'deactivated_at') ? 'deactivated_at' : null,
                Schema::hasColumn('users', 'last_login_at') ? 'last_login_at' : null,
            ]);

            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
