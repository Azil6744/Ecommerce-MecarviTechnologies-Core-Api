<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ecommerce_subscription_plans', function (Blueprint $table) {
            $columns = Schema::getColumnListing('ecommerce_subscription_plans');
            $has = fn (string $column) => in_array($column, $columns, true);

            if (! $has('internal_code')) {
                $table->string('internal_code')->nullable()->unique()->after('name');
            }
            if (! $has('account_type')) {
                $table->string('account_type', 30)->default('personal')->after('description');
            }
            if (! $has('coverage_type')) {
                $table->string('coverage_type', 30)->default('individual_site')->after('account_type');
            }
            if (! $has('applicable_site')) {
                $table->string('applicable_site')->nullable()->after('coverage_type');
            }
            if (! $has('covered_sites')) {
                $table->json('covered_sites')->nullable()->after('applicable_site');
            }
            if (! $has('include_future_sites')) {
                $table->boolean('include_future_sites')->default(false)->after('covered_sites');
            }
            if (! $has('billing_interval_count')) {
                $table->unsignedInteger('billing_interval_count')->default(1)->after('billing_cycle');
            }
            if (! $has('setup_fee')) {
                $table->decimal('setup_fee', 10, 2)->default(0)->after('price');
            }
            if (! $has('currency')) {
                $table->string('currency', 10)->default('USD')->after('setup_fee');
            }
            if (! $has('tax_treatment')) {
                $table->string('tax_treatment')->nullable()->after('currency');
            }
            if (! $has('trial_available')) {
                $table->boolean('trial_available')->default(false)->after('tax_treatment');
            }
            if (! $has('trial_duration_days')) {
                $table->unsignedInteger('trial_duration_days')->default(0)->after('trial_available');
            }
            if (! $has('trial_amount')) {
                $table->decimal('trial_amount', 10, 2)->default(0)->after('trial_duration_days');
            }
            if (! $has('introductory_price')) {
                $table->decimal('introductory_price', 10, 2)->nullable()->after('trial_amount');
            }
            if (! $has('introductory_duration_days')) {
                $table->unsignedInteger('introductory_duration_days')->default(0)->after('introductory_price');
            }
            if (! $has('annual_discount')) {
                $table->decimal('annual_discount', 10, 2)->default(0)->after('introductory_duration_days');
            }
            if (! $has('tier')) {
                $table->string('tier')->nullable()->after('members_limit');
            }
            if (! $has('image_url')) {
                $table->string('image_url')->nullable()->after('badge');
            }
            if (! $has('benefit_config')) {
                $table->json('benefit_config')->nullable()->after('features');
            }
            if (! $has('upgrade_rules')) {
                $table->json('upgrade_rules')->nullable()->after('benefit_config');
            }
            if (! $has('downgrade_rules')) {
                $table->json('downgrade_rules')->nullable()->after('upgrade_rules');
            }
            if (! $has('cancellation_rules')) {
                $table->json('cancellation_rules')->nullable()->after('downgrade_rules');
            }
            if (! $has('failed_payment_settings')) {
                $table->json('failed_payment_settings')->nullable()->after('cancellation_rules');
            }
            if (! $has('availability_rules')) {
                $table->json('availability_rules')->nullable()->after('failed_payment_settings');
            }
            if (! $has('terms')) {
                $table->text('terms')->nullable()->after('availability_rules');
            }
            if (! $has('renewal_disclosure')) {
                $table->text('renewal_disclosure')->nullable()->after('terms');
            }
            if (! $has('refund_policy')) {
                $table->text('refund_policy')->nullable()->after('renewal_disclosure');
            }
            if (! $has('cancellation_policy')) {
                $table->text('cancellation_policy')->nullable()->after('refund_policy');
            }
            if (! $has('privacy_notice')) {
                $table->text('privacy_notice')->nullable()->after('cancellation_policy');
            }
            if (! $has('requires_agreement')) {
                $table->boolean('requires_agreement')->default(true)->after('privacy_notice');
            }
            if (! $has('effective_date')) {
                $table->timestamp('effective_date')->nullable()->after('status');
            }
            if (! $has('retirement_date')) {
                $table->timestamp('retirement_date')->nullable()->after('effective_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('ecommerce_subscription_plans', function (Blueprint $table) {
            foreach ([
                'internal_code', 'account_type', 'coverage_type', 'applicable_site', 'covered_sites',
                'include_future_sites', 'billing_interval_count', 'setup_fee', 'currency',
                'tax_treatment', 'trial_available', 'trial_duration_days', 'trial_amount',
                'introductory_price', 'introductory_duration_days', 'annual_discount', 'tier',
                'image_url', 'benefit_config', 'upgrade_rules', 'downgrade_rules',
                'cancellation_rules', 'failed_payment_settings', 'availability_rules', 'terms',
                'renewal_disclosure', 'refund_policy', 'cancellation_policy', 'privacy_notice',
                'requires_agreement', 'effective_date', 'retirement_date',
            ] as $column) {
                if (Schema::hasColumn('ecommerce_subscription_plans', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
