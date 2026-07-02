<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('email_templates', 'event_key')) {
                $table->string('event_key')->nullable()->index()->after('slug');
            }
            if (! Schema::hasColumn('email_templates', 'heading')) {
                $table->string('heading')->nullable()->after('preview_text');
            }
            if (! Schema::hasColumn('email_templates', 'body_text')) {
                $table->longText('body_text')->nullable()->after('heading');
            }
            if (! Schema::hasColumn('email_templates', 'button_text')) {
                $table->string('button_text')->nullable()->after('body_text');
            }
            if (! Schema::hasColumn('email_templates', 'button_url')) {
                $table->string('button_url', 1000)->nullable()->after('button_text');
            }
            if (! Schema::hasColumn('email_templates', 'footer_text')) {
                $table->text('footer_text')->nullable()->after('button_url');
            }
            if (! Schema::hasColumn('email_templates', 'send_to_customer')) {
                $table->boolean('send_to_customer')->default(true)->after('variables');
            }
            if (! Schema::hasColumn('email_templates', 'send_to_admin')) {
                $table->boolean('send_to_admin')->default(false)->after('send_to_customer');
            }
            if (! Schema::hasColumn('email_templates', 'admin_recipients')) {
                $table->json('admin_recipients')->nullable()->after('send_to_admin');
            }
        });
    }

    public function down(): void
    {
        Schema::table('email_templates', function (Blueprint $table) {
            foreach ([
                'event_key',
                'heading',
                'body_text',
                'button_text',
                'button_url',
                'footer_text',
                'send_to_customer',
                'send_to_admin',
                'admin_recipients',
            ] as $column) {
                if (Schema::hasColumn('email_templates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
