<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PopupTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'event_key', 'trigger_type', 'trigger_pages', 'display_frequency', 'category', 'heading', 'image_url', 'logo_url', 'logo_position',
        'subtitle', 'body_text', 'body_html', 'button_text', 'button_url', 'button_style',
        'footer_text', 'status', 'variables',
        'popup_size', 'popup_position', 'overlay_opacity', 'show_close_button',
        'auto_close_seconds', 'background_color', 'text_color',
    ];

    protected $casts = [
        'variables' => 'array',
        'trigger_pages' => 'array',
        'show_close_button' => 'boolean',
        'auto_close_seconds' => 'integer',
        'overlay_opacity' => 'integer',
    ];
}

