<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SupportSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_title',
        'title',
        'description',
        'quick_support_bg_color',
        'inquiry_form_bg_color',
        'call_icon',
        'call_title',
        'call_description',
        'call_phone',
        'email_icon',
        'email_title',
        'email_description',
        'email_address',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }
}
