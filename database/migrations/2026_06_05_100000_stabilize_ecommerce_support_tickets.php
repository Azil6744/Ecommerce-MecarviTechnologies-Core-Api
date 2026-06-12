<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecommerce_tickets', function (Blueprint $table) {
            if (! Schema::hasColumn('ecommerce_tickets', 'category')) {
                $table->string('category')->nullable()->after('subject');
            }

            if (! Schema::hasColumn('ecommerce_tickets', 'contact_email')) {
                $table->string('contact_email')->nullable()->after('customer_name');
            }

            if (! Schema::hasColumn('ecommerce_tickets', 'contact_phone')) {
                $table->string('contact_phone')->nullable()->after('contact_email');
            }

            if (! Schema::hasColumn('ecommerce_tickets', 'preferred_contact_method')) {
                $table->string('preferred_contact_method', 30)->nullable()->after('contact_phone');
            }

            if (! Schema::hasColumn('ecommerce_tickets', 'is_urgent')) {
                $table->boolean('is_urgent')->default(false)->after('priority');
            }

            if (! Schema::hasColumn('ecommerce_tickets', 'last_customer_reply_at')) {
                $table->timestamp('last_customer_reply_at')->nullable()->after('metadata');
            }

            if (! Schema::hasColumn('ecommerce_tickets', 'last_staff_reply_at')) {
                $table->timestamp('last_staff_reply_at')->nullable()->after('last_customer_reply_at');
            }

            if (! Schema::hasColumn('ecommerce_tickets', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('last_staff_reply_at');
            }
        });

        Schema::create('ecommerce_ticket_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecommerce_ticket_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ecommerce_ticket_reply_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('original_name');
            $table->string('path', 1000);
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size')->default(0);
            $table->timestamps();
        });

        Schema::create('ecommerce_ticket_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecommerce_ticket_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('type', 80);
            $table->string('title');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('ecommerce_ticket_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecommerce_ticket_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->text('note');
            $table->string('visibility', 30)->default('public');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ecommerce_ticket_notes');
        Schema::dropIfExists('ecommerce_ticket_activities');
        Schema::dropIfExists('ecommerce_ticket_attachments');

        Schema::table('ecommerce_tickets', function (Blueprint $table) {
            foreach ([
                'closed_at',
                'last_staff_reply_at',
                'last_customer_reply_at',
                'is_urgent',
                'preferred_contact_method',
                'contact_phone',
                'contact_email',
                'category',
            ] as $column) {
                if (Schema::hasColumn('ecommerce_tickets', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
