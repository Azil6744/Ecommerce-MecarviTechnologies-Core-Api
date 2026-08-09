<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('popup_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('popup_templates', 'category')) {
                $table->string('category')->default('general')->after('event_key');
            }
            if (! Schema::hasColumn('popup_templates', 'subtitle')) {
                $table->string('subtitle')->nullable()->after('heading');
            }
            if (! Schema::hasColumn('popup_templates', 'body_text')) {
                $table->longText('body_text')->nullable()->after('subtitle');
            }
            if (! Schema::hasColumn('popup_templates', 'footer_text')) {
                $table->text('footer_text')->nullable()->after('button_url');
            }
            if (! Schema::hasColumn('popup_templates', 'image_url')) {
                $table->string('image_url', 1000)->nullable()->after('heading');
            }
            if (! Schema::hasColumn('popup_templates', 'logo_url')) {
                $table->string('logo_url', 1000)->nullable()->after('image_url');
            }
            if (! Schema::hasColumn('popup_templates', 'logo_position')) {
                $table->string('logo_position')->default('center')->after('logo_url');
            }
            if (! Schema::hasColumn('popup_templates', 'popup_size')) {
                $table->string('popup_size')->default('medium')->after('variables');
            }
            if (! Schema::hasColumn('popup_templates', 'popup_position')) {
                $table->string('popup_position')->default('center')->after('popup_size');
            }
            if (! Schema::hasColumn('popup_templates', 'overlay_opacity')) {
                $table->integer('overlay_opacity')->default(60)->after('popup_position');
            }
            if (! Schema::hasColumn('popup_templates', 'show_close_button')) {
                $table->boolean('show_close_button')->default(true)->after('overlay_opacity');
            }
            if (! Schema::hasColumn('popup_templates', 'auto_close_seconds')) {
                $table->integer('auto_close_seconds')->nullable()->after('show_close_button');
            }
            if (! Schema::hasColumn('popup_templates', 'button_style')) {
                $table->string('button_style')->default('primary')->after('button_url');
            }
            if (! Schema::hasColumn('popup_templates', 'background_color')) {
                $table->string('background_color')->nullable()->after('auto_close_seconds');
            }
            if (! Schema::hasColumn('popup_templates', 'text_color')) {
                $table->string('text_color')->nullable()->after('background_color');
            }
        });
    }

    public function down(): void
    {
        Schema::table('popup_templates', function (Blueprint $table) {
            $columns = [
                'category',
                'subtitle',
                'body_text',
                'footer_text',
                'image_url',
                'logo_url',
                'logo_position',
                'popup_size',
                'popup_position',
                'overlay_opacity',
                'show_close_button',
                'auto_close_seconds',
                'button_style',
                'background_color',
                'text_color',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('popup_templates', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
