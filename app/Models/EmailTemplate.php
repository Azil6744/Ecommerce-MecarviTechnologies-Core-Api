<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'event_key', 'subject', 'category',
        'preview_text', 'heading', 'body_text', 'button_text', 'button_url',
        'footer_text', 'body_html', 'status', 'variables',
        'send_to_customer', 'send_to_admin', 'admin_recipients', 'image_url',
        'logo_url', 'logo_position',
    ];

    protected $casts = [
        'variables' => 'array',
        'send_to_customer' => 'boolean',
        'send_to_admin' => 'boolean',
        'admin_recipients' => 'array',
    ];
}
