<?php

namespace Database\Seeders;

use App\Models\ContactPageHeroSection;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ContactPageHeroSectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ContactPageHeroSection::create([
            'heading' => 'Get in Touch With Us',
            'subheading' => 'We\'d Love to Hear From You',
            'description' => 'Whether you have a question about our services, pricing, or anything else, our team is ready to answer all your questions. Reach out to us and we\'ll respond as soon as we can.',
            'is_active' => true,
        ]);
    }
}
