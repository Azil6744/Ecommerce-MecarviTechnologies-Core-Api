<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, add new columns as nullable
        Schema::table('quote_form_submissions', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('estimate_budget')->nullable()->after('project_type');
            $table->string('maximum_time_for_project')->nullable()->after('estimate_budget');
            $table->text('required_skills')->nullable()->after('maximum_time_for_project');
        });

        // Migrate data from full_name to first_name/last_name if exists
        \DB::table('quote_form_submissions')->whereNotNull('full_name')->get()->each(function ($record) {
            $nameParts = explode(' ', $record->full_name, 2);
            $firstName = $nameParts[0] ?? '';
            $lastName = $nameParts[1] ?? '';
            
            \DB::table('quote_form_submissions')
                ->where('id', $record->id)
                ->update([
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ]);
        });

        // Migrate estimated_budget_range to estimate_budget if exists
        \DB::table('quote_form_submissions')
            ->whereNotNull('estimated_budget_range')
            ->update(['estimate_budget' => \DB::raw('estimated_budget_range')]);

        // Migrate estimated_timeline to maximum_time_for_project if exists
        \DB::table('quote_form_submissions')
            ->whereNotNull('estimated_timeline')
            ->update(['maximum_time_for_project' => \DB::raw('estimated_timeline')]);

        // Now drop unnecessary columns
        Schema::table('quote_form_submissions', function (Blueprint $table) {
            $table->dropColumn([
                'full_name',
                'job_title',
                'preferred_contact_method',
                'industry_sector',
                'company_size',
                'street_address',
                'city',
                'state_province',
                'postal_code',
                'business_website',
                'services_required',
                'frontend_technologies',
                'backend_technologies',
                'database_preference',
                'domain_name_ownership',
                'hosting_services_availability',
                'ready_made_product_interest',
                'custom_development_requirement',
                'brief_project_description',
                'primary_objective',
                'estimated_timeline',
                'estimated_budget_range',
                'required_integrations',
                'security_compliance_requirements',
                'ongoing_maintenance_support',
                'long_term_partnership',
                'how_did_you_hear',
                'authorization_confirmation',
            ]);
        });

        // Make first_name and last_name required (not null) if no existing data, or keep nullable
        // For safety, we'll keep them nullable since there might be existing records
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quote_form_submissions', function (Blueprint $table) {
            // Drop new fields
            $table->dropColumn([
                'first_name',
                'last_name',
                'estimate_budget',
                'maximum_time_for_project',
                'required_skills',
            ]);
        });

        Schema::table('quote_form_submissions', function (Blueprint $table) {
            // Re-add old fields (simplified - just the main ones)
            $table->string('full_name')->after('id');
            $table->string('job_title')->nullable();
            $table->string('preferred_contact_method')->nullable();
            $table->string('industry_sector')->nullable();
            $table->string('company_size')->nullable();
            $table->text('street_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state_province')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('business_website')->nullable();
            $table->json('services_required')->nullable();
            $table->json('frontend_technologies')->nullable();
            $table->json('backend_technologies')->nullable();
            $table->string('database_preference')->nullable();
            $table->string('domain_name_ownership')->nullable();
            $table->string('hosting_services_availability')->nullable();
            $table->string('ready_made_product_interest')->nullable();
            $table->string('custom_development_requirement')->nullable();
            $table->text('brief_project_description')->nullable();
            $table->string('primary_objective')->nullable();
            $table->string('estimated_timeline')->nullable();
            $table->string('estimated_budget_range')->nullable();
            $table->text('required_integrations')->nullable();
            $table->json('security_compliance_requirements')->nullable();
            $table->string('ongoing_maintenance_support')->nullable();
            $table->string('long_term_partnership')->nullable();
            $table->string('how_did_you_hear')->nullable();
            $table->boolean('authorization_confirmation')->default(false);
        });
    }
};
