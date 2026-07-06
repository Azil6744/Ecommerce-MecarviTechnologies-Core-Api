<?php

namespace Database\Seeders;

use App\Models\StorePickupLocation;
use Illuminate\Database\Seeder;

class StorePickupLocationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $weeklyHours = [
            'Monday' => ['open' => '09:00 AM', 'close' => '07:00 PM', 'breakOpen' => '01:00 PM', 'breakClose' => '02:00 PM', 'status' => 'open'],
            'Tuesday' => ['open' => '09:00 AM', 'close' => '07:00 PM', 'breakOpen' => '01:00 PM', 'breakClose' => '02:00 PM', 'status' => 'open'],
            'Wednesday' => ['open' => '09:00 AM', 'close' => '07:00 PM', 'breakOpen' => '01:00 PM', 'breakClose' => '02:00 PM', 'status' => 'open'],
            'Thursday' => ['open' => '09:00 AM', 'close' => '07:00 PM', 'breakOpen' => '01:00 PM', 'breakClose' => '02:00 PM', 'status' => 'open'],
            'Friday' => ['open' => '09:00 AM', 'close' => '07:00 PM', 'breakOpen' => '01:00 PM', 'breakClose' => '02:00 PM', 'status' => 'open'],
            'Saturday' => ['open' => '10:00 AM', 'close' => '05:00 PM', 'breakOpen' => '01:00 PM', 'breakClose' => '02:00 PM', 'status' => 'open'],
            'Sunday' => ['open' => '09:00 AM', 'close' => '05:00 PM', 'breakOpen' => '01:00 PM', 'breakClose' => '02:00 PM', 'status' => 'closed'],
        ];

        $specialHours = [
            [
                'id' => '1',
                'date' => 'May 26, 2026',
                'occasion' => 'Memorial Day',
                'open' => '10:00 AM',
                'close' => '04:00 PM',
                'status' => true
            ]
        ];

        $locations = [
            [
                'name' => 'Mecarvi Embroidery - McDonough',
                'code' => 'MEC-MCD-001',
                'store_type' => 'Company Store',
                'timezone' => '(GMT-05:00) Eastern Standard Time (EST)',
                'address' => '233 Stray Way Circle, Suite B, McDonough, GA 30253, United States',
                'phone' => '(678) 432-9876',
                'notes' => 'Headquarters flagship store.',
                'short_description' => 'Pick up your orders quickly and easily from our McDonough location.',
                'image_path' => '/assets/images/storefront_placeholder.png',
                'status' => true,
                'is_pickup_enabled' => true,
                'pickup_preparation_time' => 2,
                'pickup_preparation_unit' => 'hours',
                'max_pickup_radius' => 10.0,
                'latitude' => 33.4473,
                'longitude' => -84.1469,
                'weekly_schedule' => $weeklyHours,
                'special_hours' => $specialHours,
            ],
            [
                'name' => 'Mecarvi Embroidery - Atlanta',
                'code' => 'MEC-ATL-002',
                'store_type' => 'Company Store',
                'timezone' => '(GMT-05:00) Eastern Standard Time (EST)',
                'address' => '3650 Peachtree Rd NE, Suite 120, Atlanta, GA 30326, United States',
                'phone' => '(404) 555-0198',
                'notes' => 'Central business hub.',
                'short_description' => 'Premium Atlanta facility pickup hub.',
                'image_path' => '/assets/images/storefront_placeholder.png',
                'status' => true,
                'is_pickup_enabled' => true,
                'pickup_preparation_time' => 4,
                'pickup_preparation_unit' => 'hours',
                'max_pickup_radius' => 10.0,
                'latitude' => 33.8539,
                'longitude' => -84.3619,
                'weekly_schedule' => $weeklyHours,
                'special_hours' => [],
            ],
            [
                'name' => 'Mecarvi Embroidery - Norcross',
                'code' => 'MEC-NRX-003',
                'store_type' => 'Partner Store',
                'timezone' => '(GMT-05:00) Eastern Standard Time (EST)',
                'address' => '5865 Jimmy Carter Blvd, Suite 200, Norcross, GA 30093, United States',
                'phone' => '(770) 123-4567',
                'notes' => 'Norcross distribution center.',
                'short_description' => 'Norcross distribution and printing center.',
                'image_path' => '/assets/images/storefront_placeholder.png',
                'status' => true,
                'is_pickup_enabled' => true,
                'pickup_preparation_time' => 2,
                'pickup_preparation_unit' => 'hours',
                'max_pickup_radius' => 10.0,
                'latitude' => 33.9189,
                'longitude' => -84.1894,
                'weekly_schedule' => $weeklyHours,
                'special_hours' => [],
            ],
            [
                'name' => 'Store D',
                'code' => 'STORE-D-004',
                'store_type' => 'Company Store',
                'timezone' => '(GMT-05:00) Eastern Standard Time (EST)',
                'address' => '123 Far Away Lane, Savannah, GA 31401, United States',
                'phone' => '(912) 555-0909',
                'notes' => 'Savannah coast hub.',
                'short_description' => 'Coastal pickup center.',
                'image_path' => '/assets/images/storefront_placeholder.png',
                'status' => true,
                'is_pickup_enabled' => true,
                'pickup_preparation_time' => 24,
                'pickup_preparation_unit' => 'hours',
                'max_pickup_radius' => 10.0,
                'latitude' => 32.0809,
                'longitude' => -81.0912,
                'weekly_schedule' => $weeklyHours,
                'special_hours' => [],
            ],
            [
                'name' => 'Store E',
                'code' => 'STORE-E-005',
                'store_type' => 'Partner Store',
                'timezone' => '(GMT-05:00) Eastern Standard Time (EST)',
                'address' => '999 Remote Way, Valdosta, GA 31601, United States',
                'phone' => '(229) 555-0919',
                'notes' => 'Valdosta center.',
                'short_description' => 'Southern Georgia pickup hub.',
                'image_path' => '/assets/images/storefront_placeholder.png',
                'status' => true,
                'is_pickup_enabled' => true,
                'pickup_preparation_time' => 48,
                'pickup_preparation_unit' => 'hours',
                'max_pickup_radius' => 10.0,
                'latitude' => 30.8327,
                'longitude' => -83.2784,
                'weekly_schedule' => $weeklyHours,
                'special_hours' => [],
            ],
        ];

        foreach ($locations as $locData) {
            StorePickupLocation::updateOrCreate(
                ['code' => $locData['code']],
                $locData
            );
        }
    }
}
