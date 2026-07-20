<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceMembershipBenefit extends Model
{
    use HasFactory;

    protected $table = 'ecommerce_membership_benefits';

    protected $fillable = [
        'title',
        'description',
        'benefit_type',
        'benefit_value',
        'restrictions',
        'usage_limit',
        'reset_frequency',
        'eligible_websites',
        'eligible_products',
        'eligible_categories',
        'stacking_rules',
        'icon',
        'color',
        'background',
        'sort_order',
        'status',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'benefit_value' => 'decimal:2',
        'usage_limit' => 'integer',
        'restrictions' => 'array',
        'eligible_websites' => 'array',
        'eligible_products' => 'array',
        'eligible_categories' => 'array',
        'stacking_rules' => 'array',
    ];
}
