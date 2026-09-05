<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop Postgres check constraint if exists
        DB::statement('ALTER TABLE ecommerce_order_verifications DROP CONSTRAINT IF EXISTS ecommerce_order_verifications_status_check');
        DB::statement('ALTER TABLE ecommerce_order_verifications ALTER COLUMN status TYPE VARCHAR(50)');
        DB::statement("ALTER TABLE ecommerce_order_verifications ALTER COLUMN status SET DEFAULT 'action_required'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No down needed
    }
};
