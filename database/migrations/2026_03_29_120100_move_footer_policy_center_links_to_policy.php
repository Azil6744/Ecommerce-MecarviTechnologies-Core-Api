<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Move existing policy_center links into new "policy" type.
        DB::table('footer_links')
            ->where('type', 'policy_center')
            ->update(['type' => 'policy']);

        // Copy current policy_center heading into policy heading, then reset policy_center heading.
        $content = DB::table('footer_contents')->first();
        if ($content) {
            $policyHeading = $content->policy_section_heading ?: $content->policy_center_section_heading;
            DB::table('footer_contents')->update([
                'policy_section_heading' => $policyHeading,
                'policy_center_section_heading' => 'POLICY CENTER',
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Move policy links back to policy_center.
        DB::table('footer_links')
            ->where('type', 'policy')
            ->update(['type' => 'policy_center']);

        // Restore policy_center heading from policy_section_heading.
        $content = DB::table('footer_contents')->first();
        if ($content) {
            $policyCenterHeading = $content->policy_center_section_heading ?: $content->policy_section_heading;
            DB::table('footer_contents')->update([
                'policy_center_section_heading' => $policyCenterHeading,
            ]);
        }
    }
};
