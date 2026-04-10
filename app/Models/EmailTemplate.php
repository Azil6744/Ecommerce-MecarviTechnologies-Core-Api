<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'slug', 'subject', 'category',
        'preview_text', 'body_html', 'status', 'variables',
    ];

    protected $casts = [
        'variables' => 'array',
    ];
}
