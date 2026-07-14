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
        // SMS Settings table
        Schema::create('sms_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(false);
            $table->string('provider')->default('twilio'); // twilio, infobip, etc.
            $table->string('twilio_sid')->nullable();
            $table->text('twilio_auth_token')->nullable();
            $table->string('twilio_from_number')->nullable();
            $table->string('infobip_api_key')->nullable();
            $table->string('infobip_base_url')->nullable();
            $table->timestamps();
        });

        // Push Notification Settings table
        Schema::create('push_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('is_enabled')->default(false);
            $table->string('firebase_project_id')->nullable();
            $table->string('firebase_api_key')->nullable();
            $table->string('firebase_auth_domain')->nullable();
            $table->string('firebase_storage_bucket')->nullable();
            $table->string('firebase_messaging_sender_id')->nullable();
            $table->string('firebase_app_id')->nullable();
            $table->string('firebase_measurement_id')->nullable();
            $table->text('firebase_private_key_json')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('push_notification_settings');
        Schema::dropIfExists('sms_settings');
    }
};
