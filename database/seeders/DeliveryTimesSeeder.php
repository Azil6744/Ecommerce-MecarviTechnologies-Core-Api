<?php

namespace Database\Seeders;

use App\Models\DeliveryTime;
use Illuminate\Database\Seeder;

class DeliveryTimesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $deliveryTimes = [
            [
                'label' => 'Same Day Delivery',
                'estimated_days' => '0',
                'description' => 'Orders placed before 12 PM will be delivered on the same day.',
                'color_code' => '#28A745',
                'pricing' => 9.99,
                'priority' => 1,
                'status' => true,
            ],
            [
                'label' => 'Next Day Delivery',
                'estimated_days' => '1',
                'description' => 'Orders will be delivered the next business day.',
                'color_code' => '#007BFF',
                'pricing' => 6.99,
                'priority' => 2,
                'status' => true,
            ],
            [
                'label' => 'Express Delivery',
                'estimated_days' => '2-3',
                'description' => 'Fast delivery within 2 to 3 business days.',
                'color_code' => '#6F42C1',
                'pricing' => 12.99,
                'priority' => 3,
                'status' => true,
            ],
            [
                'label' => 'Standard Delivery',
                'estimated_days' => '3-5',
                'description' => 'Reliable delivery within 3 to 5 business days.',
                'color_code' => '#FD7E14',
                'pricing' => 4.99,
                'priority' => 4,
                'status' => true,
            ],
            [
                'label' => 'Economy Delivery',
                'estimated_days' => '5-7',
                'description' => 'Budget-friendly delivery within 5 to 7 business days.',
                'color_code' => '#6C757D',
                'pricing' => 2.99,
                'priority' => 5,
                'status' => true,
            ],
            [
                'label' => 'International Delivery',
                'estimated_days' => '7-14',
                'description' => 'Estimated delivery time for international orders.',
                'color_code' => '#17A2B8',
                'pricing' => 19.99,
                'priority' => 6,
                'status' => true,
            ],
        ];

        foreach ($deliveryTimes as $dt) {
            DeliveryTime::updateOrCreate(
                ['label' => $dt['label']],
                $dt
            );
        }
    }
}
