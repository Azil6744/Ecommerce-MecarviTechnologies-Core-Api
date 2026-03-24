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
        Schema::create('quote_form_submissions', function (Blueprint $table) {
            $table->id();
            
            // Primary Contact Information
            $table->string('full_name');
            $table->string('job_title')->nullable();
            $table->string('company_name')->nullable();
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('preferred_contact_method')->nullable();
            
            // Company & Business Details
            $table->string('industry_sector')->nullable();
            $table->string('company_size')->nullable();
            $table->text('street_address')->nullable();
            $table->string('city')->nullable();
            $table->string('state_province')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('business_website')->nullable();
            
            // Services Required (JSON array for checkboxes)
            $table->json('services_required')->nullable();
            
            // Front-End Technologies (JSON array for checkboxes)
            $table->json('frontend_technologies')->nullable();
            
            // Back-End Technologies (JSON array for checkboxes)
            $table->json('backend_technologies')->nullable();
            
            // Database Preference (Radio - single selection)
            $table->string('database_preference')->nullable();
            
            // Domain Name Ownership (Radio - single selection)
            $table->string('domain_name_ownership')->nullable();
            
            // Hosting Services Availability (Radio - single selection)
            $table->string('hosting_services_availability')->nullable();
            
            // Interest in Ready-Made Product Solutions (Radio - single selection)
            $table->string('ready_made_product_interest')->nullable();
            
            // Custom Development Requirement (Radio - single selection)
            $table->string('custom_development_requirement')->nullable();
            
            // Project Type (Dropdown)
            $table->string('project_type')->nullable();
            
            // Brief Project Description (Textarea)
            $table->text('brief_project_description')->nullable();
            
            // Primary Objective (Dropdown)
            $table->string('primary_objective')->nullable();
            
            // Estimated Timeline (Dropdown)
            $table->string('estimated_timeline')->nullable();
            
            // Estimated Budget Range (Dropdown - optional)
            $table->string('estimated_budget_range')->nullable();
            
            // Required Integrations (Textarea)
            $table->text('required_integrations')->nullable();
            
            // Security or Compliance Requirements (JSON array for checkboxes)
            $table->json('security_compliance_requirements')->nullable();
            
            // Ongoing maintenance and support (Radio - single selection)
            $table->string('ongoing_maintenance_support')->nullable();
            
            // Long-term technology partnership (Radio - single selection)
            $table->string('long_term_partnership')->nullable();
            
            // How did you hear about Mecarvi Technologies? (Text input)
            $table->string('how_did_you_hear')->nullable();
            
            // Upload Files (File path)
            $table->string('uploaded_files')->nullable();
            
            // Message (Textarea)
            $table->text('message')->nullable();
            
            // Authorization & Accuracy Confirmation (Checkbox)
            $table->boolean('authorization_confirmation')->default(false);
            
            // Admin fields
            $table->boolean('is_read')->default(false);
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quote_form_submissions');
    }
};
