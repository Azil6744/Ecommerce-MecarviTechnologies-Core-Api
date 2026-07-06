<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('channel', 20)->index();
            $table->string('name');
            $table->string('audience_type', 30)->default('segment');
            $table->string('segment')->nullable();
            $table->unsignedInteger('recipients_count')->default(0);
            $table->json('custom_recipients')->nullable();
            $table->string('from_name')->nullable();
            $table->string('from_email')->nullable();
            $table->string('reply_to')->nullable();
            $table->string('subject')->nullable();
            $table->string('preview_text', 500)->nullable();
            $table->string('content_type')->nullable();
            $table->longText('body')->nullable();
            $table->string('sender_name')->nullable();
            $table->string('sender_phone')->nullable();
            $table->string('notification_title')->nullable();
            $table->text('notification_message')->nullable();
            $table->string('deep_link', 1000)->nullable();
            $table->json('platforms')->nullable();
            $table->string('image_path')->nullable();
            $table->string('schedule_type', 30)->default('now');
            $table->timestamp('scheduled_at')->nullable();
            $table->string('timezone')->nullable();
            $table->string('status', 30)->default('draft')->index();
            $table->json('settings')->nullable();
            $table->json('metrics')->nullable();
            $table->json('last_test')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_campaigns');
    }
};
