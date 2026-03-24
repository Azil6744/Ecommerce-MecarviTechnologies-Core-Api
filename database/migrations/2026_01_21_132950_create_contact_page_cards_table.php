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
        Schema::create('contact_page_cards', function (Blueprint $table) {
            $table->id();
            $table->string('card_type'); // call, fax, email, visit, store_hours, online_hours
            $table->string('badge_title')->nullable();
            $table->string('secondary_badge')->nullable(); // For email card
            $table->string('label')->nullable();
            
            // Call card fields
            $table->string('phone_number_1')->nullable();
            $table->string('phone_number_2')->nullable();
            
            // Fax card field
            $table->string('fax_number')->nullable();
            
            // Email card field
            $table->string('email_address')->nullable();
            
            // Visit card fields
            $table->text('street_address')->nullable();
            $table->string('state_postal_code')->nullable();
            $table->string('country')->nullable();
            
            // Hours cards fields
            $table->string('monday_friday_hours')->nullable();
            $table->string('saturday_hours')->nullable();
            $table->string('sunday_hours')->nullable();
            
            // Common fields
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contact_page_cards');
    }
};
