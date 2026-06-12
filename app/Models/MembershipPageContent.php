<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipPageContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'backgrounds',
        'hero',
        'stats',
        'plan_section',
        'plans',
        'benefits_section',
        'benefits',
        'bottom_cta',
        'faq_section',
        'faqs',
        'support_section',
    ];

    protected $casts = [
        'backgrounds' => 'array',
        'hero' => 'array',
        'stats' => 'array',
        'plan_section' => 'array',
        'plans' => 'array',
        'benefits_section' => 'array',
        'benefits' => 'array',
        'bottom_cta' => 'array',
        'faq_section' => 'array',
        'faqs' => 'array',
        'support_section' => 'array',
    ];
}
