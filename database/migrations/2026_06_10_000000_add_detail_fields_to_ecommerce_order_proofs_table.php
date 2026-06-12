<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecommerce_order_proofs', function (Blueprint $table) {
            $table->string('title')->nullable()->after('proof_type');
            $table->string('preview_file_path')->nullable()->after('file_path');
            $table->timestamp('expires_at')->nullable()->after('preview_file_path');
            $table->timestamp('approved_at')->nullable()->after('status');
            $table->timestamp('rejected_at')->nullable()->after('approved_at');
            $table->timestamp('reviewed_at')->nullable()->after('rejected_at');
        });
    }

    public function down(): void
    {
        Schema::table('ecommerce_order_proofs', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'preview_file_path',
                'expires_at',
                'approved_at',
                'rejected_at',
                'reviewed_at',
            ]);
        });
    }
};
