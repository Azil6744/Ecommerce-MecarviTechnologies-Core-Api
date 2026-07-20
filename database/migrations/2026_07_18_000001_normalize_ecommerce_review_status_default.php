<?php

use App\Models\EcommerceReview;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('ecommerce_reviews') || ! Schema::hasColumn('ecommerce_reviews', 'status')) {
            return;
        }

        DB::table('ecommerce_reviews')
            ->whereRaw('LOWER(status) = ?', [EcommerceReview::STATUS_PENDING])
            ->update(['status' => EcommerceReview::STATUS_PENDING]);

        DB::table('ecommerce_reviews')
            ->whereRaw('LOWER(status) = ?', [EcommerceReview::STATUS_APPROVED])
            ->update(['status' => EcommerceReview::STATUS_APPROVED]);

        DB::table('ecommerce_reviews')
            ->whereRaw('LOWER(status) = ?', [EcommerceReview::STATUS_REJECTED])
            ->update(['status' => EcommerceReview::STATUS_REJECTED]);

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE ecommerce_reviews ALTER COLUMN status SET DEFAULT 'pending'");
            return;
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE ecommerce_reviews ALTER COLUMN status SET DEFAULT 'pending'");
            return;
        }

        if ($driver === 'sqlite') {
            return;
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('ecommerce_reviews') || ! Schema::hasColumn('ecommerce_reviews', 'status')) {
            return;
        }

        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            DB::statement("ALTER TABLE ecommerce_reviews ALTER COLUMN status SET DEFAULT 'Pending'");
            return;
        }

        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE ecommerce_reviews ALTER COLUMN status SET DEFAULT 'Pending'");
        }
    }
};
