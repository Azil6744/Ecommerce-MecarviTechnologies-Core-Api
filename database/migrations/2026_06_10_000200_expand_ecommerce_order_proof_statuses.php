<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->syncStatusConstraint(['awaiting_approval', 'approved', 'rejected', 'revision_requested']);
    }

    public function down(): void
    {
        $this->syncStatusConstraint(['awaiting_approval', 'approved', 'rejected']);
    }

    private function syncStatusConstraint(array $allowedValues): void
    {
        $driver = Schema::getConnection()->getDriverName();
        $quotedValues = implode(', ', array_map(fn (string $value) => "'" . $value . "'", $allowedValues));

        if ($driver === 'pgsql') {
            $constraints = DB::select("
                SELECT c.conname
                FROM pg_constraint c
                JOIN pg_class t ON c.conrelid = t.oid
                JOIN pg_attribute a ON a.attrelid = t.oid
                WHERE t.relname = 'ecommerce_order_proofs'
                  AND a.attname = 'status'
                  AND a.attnum = ANY (c.conkey)
                  AND c.contype = 'c'
            ");

            foreach ($constraints as $constraint) {
                DB::statement(sprintf('ALTER TABLE ecommerce_order_proofs DROP CONSTRAINT IF EXISTS "%s"', $constraint->conname));
            }

            DB::statement("ALTER TABLE ecommerce_order_proofs ALTER COLUMN status TYPE VARCHAR(255) USING status::text");
            DB::statement("ALTER TABLE ecommerce_order_proofs ALTER COLUMN status SET DEFAULT 'awaiting_approval'");
            DB::statement("ALTER TABLE ecommerce_order_proofs ADD CONSTRAINT ecommerce_order_proofs_status_check CHECK (status IN ($quotedValues))");
            return;
        }

        DB::statement("ALTER TABLE ecommerce_order_proofs MODIFY status ENUM($quotedValues) DEFAULT 'awaiting_approval'");
    }
};
