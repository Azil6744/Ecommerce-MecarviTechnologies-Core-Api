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
            $table->string('recipient_phone')->nullable()->after('recipient_email');
            $table->string('design_theme')->default('default')->after('currency');
            $table->boolean('allow_partial_redemption')->default(true)->after('design_theme');
            $table->boolean('restrict_first_redemption')->default(false)->after('allow_partial_redemption');
            $table->boolean('notify_on_redemption')->default(false)->after('restrict_first_redemption');
            $table->text('internal_notes')->nullable()->after('notify_on_redemption');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ecommerce_gift_cards', function (Blueprint $table) {
            $table->dropColumn([
                'recipient_phone',
                'design_theme',
                'allow_partial_redemption',
                'restrict_first_redemption',
                'notify_on_redemption',
                'internal_notes'
            ]);
        });
    }
};
