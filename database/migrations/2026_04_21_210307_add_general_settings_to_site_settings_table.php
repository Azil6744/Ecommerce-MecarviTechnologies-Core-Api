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
        Schema::table('site_settings', function (Blueprint $table) {
            $table->text('confirmation_message')->nullable();
            $table->text('default_message')->nullable();
            $table->string('loader_type')->nullable();
            $table->string('loader_color')->nullable();
            $table->boolean('maintenance_mode')->default(false);
            $table->text('maintenance_message')->nullable();
            $table->string('contact_us_email')->nullable();
            $table->string('contact_us_phone')->nullable();
            $table->text('contact_us_address')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('site_settings', function (Blueprint $table) {
            $table->dropColumn([
                'confirmation_message',
                'default_message',
                'loader_type',
                'loader_color',
                'maintenance_mode',
                'maintenance_message',
                'contact_us_email',
                'contact_us_phone',
                'contact_us_address',
            ]);
        });
    }
};
