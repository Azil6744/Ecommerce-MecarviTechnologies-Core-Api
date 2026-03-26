<?php

namespace Database\Seeders;

use App\Models\QuoteFormField;
use Illuminate\Database\Seeder;

class QuoteFormFieldSeeder extends Seeder
{
    public function run(): void
    {
        $fields = [
            // ── Section 1: Company ──────────────────────────────────────
            ['section' => 'Company', 'label' => 'Legal Company Name *', 'name' => 'legalCompanyName', 'type' => 'text', 'is_required' => true, 'sort_order' => 1, 'grid_cols' => 2],
            ['section' => 'Company', 'label' => 'Type of Organization', 'name' => 'organizationType', 'type' => 'select', 'options' => ['Franchise Operator', 'Multi-Location Retail', 'Commercial Developer', 'Property Owner', 'General Contractor', 'Corporate Brand', 'Government', 'Other'], 'sort_order' => 2, 'grid_cols' => 2],
            ['section' => 'Company', 'label' => 'Full Name *', 'name' => 'contactFullName', 'type' => 'text', 'is_required' => true, 'sort_order' => 3, 'grid_cols' => 2],
            ['section' => 'Company', 'label' => 'Title *', 'name' => 'contactTitle', 'type' => 'text', 'is_required' => true, 'sort_order' => 4, 'grid_cols' => 2],
            ['section' => 'Company', 'label' => 'Business Email *', 'name' => 'contactBusinessEmail', 'type' => 'email', 'is_required' => true, 'sort_order' => 5, 'grid_cols' => 2],
            ['section' => 'Company', 'label' => 'Direct Phone *', 'name' => 'contactDirectPhone', 'type' => 'tel', 'is_required' => true, 'sort_order' => 6, 'grid_cols' => 2],
            ['section' => 'Company', 'label' => 'Primary Communication Preference', 'name' => 'communicationPreference', 'type' => 'radio', 'options' => ['Email', 'Phone', 'Teams / Zoom'], 'sort_order' => 7, 'grid_cols' => 1],

            // ── Section 2: Scope ────────────────────────────────────────
            ['section' => 'Scope', 'label' => 'Project Type *', 'name' => 'projectType', 'type' => 'radio', 'options' => ['New Development', 'Rebrand Program', 'Location Conversion', 'Prototype Rollout', 'One Location', 'Multi-Location Program'], 'is_required' => true, 'sort_order' => 1, 'grid_cols' => 1],
            ['section' => 'Scope', 'label' => 'Total Locations Included *', 'name' => 'totalLocations', 'type' => 'number', 'is_required' => true, 'sort_order' => 2, 'grid_cols' => 1],
            ['section' => 'Scope', 'label' => 'Upload Location List (CSV or Excel)', 'name' => 'locationListFile', 'type' => 'file', 'sort_order' => 3, 'grid_cols' => 1],
            ['section' => 'Scope', 'label' => 'Geographic Coverage *', 'name' => 'geographicCoverage', 'type' => 'checkbox', 'options' => ['Single State', 'Multi-State', 'Nationwide', 'International'], 'is_required' => true, 'sort_order' => 4, 'grid_cols' => 1],

            // ── Section 3: Signage ──────────────────────────────────────
            ['section' => 'Signage', 'label' => 'Sign Categories Required', 'name' => 'signCategories', 'type' => 'checkbox', 'options' => ['Canopy Signs', 'Post and Panel Signs', 'Billboards', 'Monument Signs', 'Pylon Signs', 'Interior Lobby Signs', 'Directional and Wayfinding', 'Window Graphics', 'Wall Graphics', 'Floor Graphics', 'Fleet Graphics', 'Other'], 'sort_order' => 1, 'grid_cols' => 1],
            ['section' => 'Signage', 'label' => 'Illumination Standard', 'name' => 'illuminationStandard', 'type' => 'radio', 'options' => ['Illuminated Required', 'Non-Illuminated', 'Mixed', 'Undetermined'], 'sort_order' => 2, 'grid_cols' => 1],
            ['section' => 'Signage', 'label' => 'Scope of Work', 'name' => 'scopeOfWork', 'type' => 'radio', 'options' => ['Fabrication Only', 'Fabrication and Installation', 'Turnkey Program (Design, Engineering, Permitting, Fabrication and Installation)', 'Engineering Support Only'], 'sort_order' => 3, 'grid_cols' => 1],
            ['section' => 'Signage', 'label' => 'Brand Documentation', 'name' => 'brandDocumentationStatus', 'type' => 'radio', 'options' => ['Brand Guidelines Available', 'Prototype Exists', 'Engineering Review Required', 'Full Design Development Required'], 'sort_order' => 4, 'grid_cols' => 1],
            ['section' => 'Signage', 'label' => 'Upload Brand Documents', 'name' => 'brandDocuments', 'type' => 'file', 'sort_order' => 5, 'grid_cols' => 1],

            // ── Section 4: Operations ───────────────────────────────────
            ['section' => 'Operations', 'label' => 'Permitting Responsibility', 'name' => 'permittingResponsibility', 'type' => 'radio', 'options' => ['Mecarvi to Manage Permits', 'Client Will Manage Permits', 'Shared Responsibility'], 'sort_order' => 1, 'grid_cols' => 1],
            ['section' => 'Operations', 'label' => 'Installation Coordination', 'name' => 'installationCoordination', 'type' => 'radio', 'options' => ['Work During Business Hours', 'After Hours Required', 'General Contractor Coordination Required', 'Phased Deployment Required'], 'sort_order' => 2, 'grid_cols' => 1],

            // ── Section 5: Timeline ─────────────────────────────────────
            ['section' => 'Timeline', 'label' => 'Target Timeline', 'name' => 'targetTimeline', 'type' => 'select', 'options' => ['Immediate Execution', 'Within 30 Days', '60-90 Days', 'Budget Planning Phase'], 'sort_order' => 1, 'grid_cols' => 1],
            ['section' => 'Timeline', 'label' => 'Deployment Strategy', 'name' => 'deploymentStrategy', 'type' => 'radio', 'options' => ['Simultaneous Deployment', 'Regional Phased Rollout', 'Pilot Location First', 'Undetermined'], 'sort_order' => 2, 'grid_cols' => 1],

            // ── Section 6: Financial ────────────────────────────────────
            ['section' => 'Financial', 'label' => 'Estimated Program Budget', 'name' => 'estimatedProgramBudget', 'type' => 'radio', 'options' => ['Under $2,500', '$2,501-$5,000', '$5,001-$10,000', '$10,001-$25,000', '$25,001-$50,000', '$50,001-$100,000', '$100,000+', 'Not Sure'], 'sort_order' => 1, 'grid_cols' => 1],
            ['section' => 'Financial', 'label' => 'Procurement Method', 'name' => 'procurementMethod', 'type' => 'radio', 'options' => ['Direct Award', 'Competitive Bid', 'RFP Issuance', 'Vendor Qualification Stage'], 'sort_order' => 2, 'grid_cols' => 1],
            ['section' => 'Financial', 'label' => 'Payment Terms', 'name' => 'paymentTerms', 'type' => 'radio', 'options' => ['Net 30', 'Net 45', 'Net 60', 'Milestone Billing', 'Open to Proposal'], 'sort_order' => 3, 'grid_cols' => 1],

            // ── Section 7: Support ──────────────────────────────────────
            ['section' => 'Support', 'label' => 'Project Manager', 'name' => 'stakeholderProjectManager', 'type' => 'text', 'sort_order' => 1, 'grid_cols' => 2],
            ['section' => 'Support', 'label' => 'Facilities Contact', 'name' => 'stakeholderFacilitiesContact', 'type' => 'text', 'sort_order' => 2, 'grid_cols' => 2],
            ['section' => 'Support', 'label' => 'Brand Director', 'name' => 'stakeholderBrandDirector', 'type' => 'text', 'sort_order' => 3, 'grid_cols' => 2],
            ['section' => 'Support', 'label' => 'General Contractor', 'name' => 'stakeholderGeneralContractor', 'type' => 'text', 'sort_order' => 4, 'grid_cols' => 2],
            ['section' => 'Support', 'label' => 'Ongoing Service Programs', 'name' => 'ongoingServicePrograms', 'type' => 'checkbox', 'options' => ['National Maintenance Program', 'Emergency Repair SLA', 'Warranty Management', 'Annual Inspection Program'], 'sort_order' => 5, 'grid_cols' => 1],
            ['section' => 'Support', 'label' => 'Project Add-Ons', 'name' => 'projectAddOns', 'type' => 'checkbox', 'options' => ['Site Survey', 'Structural Engineering', 'Electrical Work', 'Sign Removal and Disposal'], 'sort_order' => 6, 'grid_cols' => 1],
            ['section' => 'Support', 'label' => 'Special Compliance Requirements', 'name' => 'specialComplianceRequirements', 'type' => 'textarea', 'sort_order' => 7, 'grid_cols' => 1],

            // ── Section 8: Authorization ────────────────────────────────
            ['section' => 'Authorization', 'label' => 'I confirm I have authority to request this proposal on behalf of the organization.', 'name' => 'confirmAuthority', 'type' => 'checkbox', 'is_required' => true, 'sort_order' => 1, 'grid_cols' => 1],
            ['section' => 'Authorization', 'label' => 'I agree to be contacted regarding this project.', 'name' => 'agreeToContact', 'type' => 'checkbox', 'is_required' => true, 'sort_order' => 2, 'grid_cols' => 1],
        ];

        foreach ($fields as $field) {
            QuoteFormField::updateOrCreate(
                ['name' => $field['name'], 'page_slug' => 'quote'],
                array_merge([
                    'page_slug' => 'quote',
                    'is_required' => false,
                    'is_active' => true,
                    'placeholder' => null,
                    'options' => null,
                    'config' => null,
                    'grid_cols' => 1,
                ], $field)
            );
        }
    }
}
