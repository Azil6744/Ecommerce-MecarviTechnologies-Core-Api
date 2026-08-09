<?php

namespace Database\Seeders;

use App\Services\PopupTemplateService;
use Illuminate\Database\Seeder;

class PopupTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $service = new PopupTemplateService();
        $service->ensureDefaultTemplates();
        $this->command->info('✓ Popup templates seeded: ' . \App\Models\PopupTemplate::count());
    }
}
