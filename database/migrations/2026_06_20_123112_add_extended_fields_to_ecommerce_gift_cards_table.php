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
        Schema::table('ecommerce_gift_cards', function (Blueprint $table) {
            $table->unsignedBigInteger('buyer_user_id')->nullable()->after('order_id');
            $table->string('buyer_name')->nullable()->after('buyer_user_id');
            $table->string('buyer_email')->nullable()->after('buyer_name');
            $table->string('owner_email')->nullable()->after('buyer_email');
            $table->enum('issue_type', ['Purchased', 'Manual'])->default('Purchased')->after('owner_email');
            $table->unsignedBigInteger('issued_by_admin_id')->nullable()->after('issue_type');
            $table->text('disabled_reason')->nullable()->after('issued_by_admin_id');
            $table->dateTime('last_used_at')->nullable()->after('disabled_reason');

            $table->foreign('buyer_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('issued_by_admin_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecommerce_gift_cards', function (Blueprint $table) {
            $table->dropForeign(['buyer_user_id']);
            $table->dropForeign(['issued_by_admin_id']);

            $table->dropColumn([
                'buyer_user_id',
                'buyer_name',
                'buyer_email',
                'owner_email',
                'issue_type',
                'issued_by_admin_id',
                'disabled_reason',
                'last_used_at',
            ]);
        });
    }
};
