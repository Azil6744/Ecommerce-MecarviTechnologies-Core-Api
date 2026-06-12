<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftCardPageContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'backgrounds',
        'header',
        'hero',
        'perks',
        'card_types_section',
        'card_types',
        'design_showcase',
        'how_it_works',
        'redeem_band',
        'faq_section',
        'faqs',
        'support_section',
        'bottom_cta',
    ];

    protected $casts = [
        'backgrounds' => 'array',
        'header' => 'array',
        'hero' => 'array',
        'perks' => 'array',
        'card_types_section' => 'array',
        'card_types' => 'array',
        'design_showcase' => 'array',
        'how_it_works' => 'array',
        'redeem_band' => 'array',
        'faq_section' => 'array',
        'faqs' => 'array',
        'support_section' => 'array',
        'bottom_cta' => 'array',
    ];
}
