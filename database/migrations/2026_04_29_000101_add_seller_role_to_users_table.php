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
        if (Schema::connection($this->getConnection())->getConnection()->getDriverName() === 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });

            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['super_admin', 'admin', 'editor', 'viewer', 'customer', 'seller'])
                    ->default('viewer')
                    ->after('password');
            });
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
        if (Schema::connection($this->getConnection())->getConnection()->getDriverName() === 'sqlite') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });

            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['super_admin', 'admin', 'editor', 'viewer', 'customer'])
                    ->default('viewer')
                    ->after('password');
            });
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['super_admin', 'admin', 'editor', 'viewer', 'customer'])
                    ->default('viewer')
                    ->change();
            });
        }
    }
};
