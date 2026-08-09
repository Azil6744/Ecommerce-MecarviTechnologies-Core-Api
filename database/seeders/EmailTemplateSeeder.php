<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Services\EmailNotificationService;

class EmailTemplateSeeder extends Seeder
{
    /**
     * Seed all 49 default email templates into the database.
     */
    public function run(): void
    {
        $service = app(EmailNotificationService::class);
        $service->ensureDefaultTemplates();

        $this->command->info('✓ All 49 email templates seeded successfully.');
    }
}
