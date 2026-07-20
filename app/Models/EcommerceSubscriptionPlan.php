<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcommerceSubscriptionPlan extends Model
{
    use HasFactory;

    protected $table = 'ecommerce_subscription_plans';

    protected $fillable = [
        'name',
        'internal_code',
        'description',
        'account_type',
        'coverage_type',
        'applicable_site',
        'covered_sites',
        'include_future_sites',
        'price',
        'billing_cycle',
        'billing_interval_count',
        'setup_fee',
        'currency',
        'tax_treatment',
        'trial_available',
        'trial_duration_days',
        'trial_amount',
        'introductory_price',
        'introductory_duration_days',
        'annual_discount',
        'members_limit',
        'tier',
        'features',
        'benefit_config',
        'upgrade_rules',
        'downgrade_rules',
        'cancellation_rules',
        'failed_payment_settings',
        'availability_rules',
        'terms',
        'renewal_disclosure',
        'refund_policy',
        'cancellation_policy',
        'privacy_notice',
        'requires_agreement',
        'badge',
        'image_url',
        'sort_order',
        'status',
        'effective_date',
        'retirement_date',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'trial_available' => 'boolean',
        'trial_duration_days' => 'integer',
        'trial_amount' => 'decimal:2',
        'introductory_price' => 'decimal:2',
        'introductory_duration_days' => 'integer',
        'annual_discount' => 'decimal:2',
        'members_limit' => 'integer',
        'covered_sites' => 'array',
        'include_future_sites' => 'boolean',
        'billing_interval_count' => 'integer',
        'features' => 'array',
        'benefit_config' => 'array',
        'upgrade_rules' => 'array',
        'downgrade_rules' => 'array',
        'cancellation_rules' => 'array',
        'failed_payment_settings' => 'array',
        'availability_rules' => 'array',
        'requires_agreement' => 'boolean',
        'sort_order' => 'integer',
        'effective_date' => 'datetime',
        'retirement_date' => 'datetime',
    ];
}
