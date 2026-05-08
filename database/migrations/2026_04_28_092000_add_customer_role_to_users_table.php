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
        // For SQLite, we need to recreate the table with the new enum values
        if (Schema::connection($this->getConnection())->getConnection()->getDriverName() === 'sqlite') {
            // SQLite doesn't support ALTER TABLE for enum, so we need to recreate
            Schema::table('users', function (Blueprint $table) {
                // Drop the old role column
                $table->dropColumn('role');
            });
            
            Schema::table('users', function (Blueprint $table) {
                // Add new role column with customer option
                $table->enum('role', ['super_admin', 'editor', 'viewer', 'customer'])->default('viewer')->after('password');
            });
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
        if (Schema::connection($this->getConnection())->getConnection()->getDriverName() === 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
            
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['super_admin', 'editor', 'viewer'])->default('viewer')->after('password');
            });
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['super_admin', 'editor', 'viewer'])->default('viewer')->change();
            });
        }
    }
};
