<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_brands', function (Blueprint $table) {
            $table->string('logo')->nullable()->after('is_active');
            $table->string('seo_meta_title')->nullable()->after('logo');
            $table->text('seo_meta_description')->nullable()->after('seo_meta_title');
        });
    }

    public function down(): void
    {
        Schema::table('product_brands', function (Blueprint $table) {
            $table->dropColumn(['logo', 'seo_meta_title', 'seo_meta_description']);
        });
    }
};
