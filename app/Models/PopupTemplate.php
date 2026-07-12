<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PopupTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'event_key',
        'heading',
        'body_html',
        'button_text',
        'button_url',
        'status',
        'variables',
    ];

    protected $casts = [
        'variables' => 'array',
    ];
}

