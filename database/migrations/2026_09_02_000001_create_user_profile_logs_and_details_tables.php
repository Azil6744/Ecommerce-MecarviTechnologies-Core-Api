<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'address')) {
                $table->string('address')->nullable();
            }
            if (!Schema::hasColumn('users', 'dob')) {
                $table->date('dob')->nullable();
            }
            if (!Schema::hasColumn('users', 'gender')) {
                $table->string('gender')->nullable();
            }
            if (!Schema::hasColumn('users', 'membership_status')) {
                $table->string('membership_status')->nullable()->default('Basic Lite');
            }
            if (!Schema::hasColumn('users', 'customer_account_number')) {
                $table->string('customer_account_number')->nullable()->unique();
            }
            if (!Schema::hasColumn('users', 'business_name')) {
                $table->string('business_name')->nullable();
            }
            if (!Schema::hasColumn('users', 'tax_id')) {
                $table->string('tax_id')->nullable();
            }
            if (!Schema::hasColumn('users', 'business_type')) {
                $table->string('business_type')->nullable();
            }
        });

        if (!Schema::hasTable('user_login_histories')) {
            Schema::create('user_login_histories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->string('ip_address')->nullable();
                $table->string('device_type')->default('desktop'); // desktop, mobile, tablet
                $table->string('device_title')->nullable(); // e.g. Chrome on Windows
                $table->text('device_details')->nullable(); // e.g. Windows 11 \n Chrome 125.0
                $table->string('location')->nullable(); // e.g. Grand Anse, St. George's Grenada, W.I.
                $table->string('network')->nullable(); // e.g. ISP, Local Network
                $table->string('status')->default('Successful'); // Successful, Failed
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('user_admin_changes')) {
            Schema::create('user_admin_changes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('admin_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('actor_name')->default('Admin');
                $table->string('actor_role')->default('Administrator');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('changed_fields')->nullable();
                $table->text('before_value')->nullable();
                $table->text('after_value')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('donations', function (Blueprint $table) {
            if (!Schema::hasColumn('donations', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_admin_changes');
        Schema::dropIfExists('user_login_histories');

        Schema::table('donations', function (Blueprint $table) {
            if (Schema::hasColumn('donations', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $cols = ['address', 'dob', 'gender', 'membership_status', 'customer_account_number', 'business_name', 'tax_id', 'business_type'];
            foreach ($cols as $c) {
                if (Schema::hasColumn('users', $c)) {
                    $table->dropColumn($c);
                }
            }
        });
    }
};
