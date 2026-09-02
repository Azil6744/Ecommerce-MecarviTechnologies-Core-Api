<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\EcommerceCustomerVerification;
use Illuminate\Support\Facades\Hash;

class EcommerceCustomerVerificationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Ensure Customer Users exist
        $azil = User::firstOrCreate(
            ['email' => 'developmentwithazil@gmail.com'],
            [
                'name' => 'Azil Adil',
                'username' => 'aziladil',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+923206458532',
                'avatar' => 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=150&auto=format&fit=crop&q=80',
            ]
        );

        $marcus = User::firstOrCreate(
            ['email' => 'marcus@email.com'],
            [
                'name' => 'Marcus Thomas',
                'username' => 'marcusthomas',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+1 473 405 8720',
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
            ]
        );

        $sarah = User::firstOrCreate(
            ['email' => 'sarah@email.com'],
            [
                'name' => 'Sarah Johnson',
                'username' => 'sarahjohnson',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+1 473 418 0720',
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80',
            ]
        );

        $david = User::firstOrCreate(
            ['email' => 'david@email.com'],
            [
                'name' => 'David Kim',
                'username' => 'davidkim',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+1 473 468 0720',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
            ]
        );

        $jessica = User::firstOrCreate(
            ['email' => 'jessica@email.com'],
            [
                'name' => 'Jessica Brown',
                'username' => 'jessicabrown',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+1 473 206 4585',
                'avatar' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=150&auto=format&fit=crop&q=80',
            ]
        );

        $robert = User::firstOrCreate(
            ['email' => 'robert@email.com'],
            [
                'name' => 'Robert Wilson',
                'username' => 'robertwilson',
                'password' => Hash::make('password'),
                'role' => 'customer',
                'phone' => '+1 473 456 7890',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80',
            ]
        );

        // 2. Ensure Business Seller Users exist
        $bizMarcus = User::firstOrCreate(
            ['email' => 'marcus@stitchpro.com'],
            [
                'name' => 'Marcus Thomas',
                'username' => 'stitchpro',
                'password' => Hash::make('password'),
                'role' => 'seller',
                'phone' => '+1 473 405 8720',
                'avatar' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=150&auto=format&fit=crop&q=80',
            ]
        );

        $bizSarah = User::firstOrCreate(
            ['email' => 'sarah@eliteembroidery.com'],
            [
                'name' => 'Sarah Johnson',
                'username' => 'eliteembroidery',
                'password' => Hash::make('password'),
                'role' => 'seller',
                'phone' => '+1 473 418 0720',
                'avatar' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=150&auto=format&fit=crop&q=80',
            ]
        );

        $bizDavid = User::firstOrCreate(
            ['email' => 'david@threadsprints.com'],
            [
                'name' => 'David Kim',
                'username' => 'threadsprints',
                'password' => Hash::make('password'),
                'role' => 'seller',
                'phone' => '+1 473 468 0720',
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=150&auto=format&fit=crop&q=80',
            ]
        );

        $bizJessica = User::firstOrCreate(
            ['email' => 'jessica@creativethreads.com'],
            [
                'name' => 'Jessica Brown',
                'username' => 'creativethreads',
                'password' => Hash::make('password'),
                'role' => 'seller',
                'phone' => '+1 473 206 4585',
                'avatar' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=150&auto=format&fit=crop&q=80',
            ]
        );

        $bizRobert = User::firstOrCreate(
            ['email' => 'robert@sewnright.com'],
            [
                'name' => 'Robert Wilson',
                'username' => 'sewnright',
                'password' => Hash::make('password'),
                'role' => 'seller',
                'phone' => '+1 473 456 7890',
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=150&auto=format&fit=crop&q=80',
            ]
        );

        // 3. Clear existing verification records to avoid duplicate seeds
        EcommerceCustomerVerification::truncate();

        // 4. Seed Customer Verification Submissions
        EcommerceCustomerVerification::create([
            'user_id' => $azil->id,
            'document_type' => 'Identity Verification (Passport)',
            'document_path' => 'verifications/azil_passport.pdf',
            'status' => 'pending',
            'notes' => "[Sep 01, 2026 14:00] Initial passport photo scan submitted for customer verification.",
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ]);

        EcommerceCustomerVerification::create([
            'user_id' => $marcus->id,
            'document_type' => 'Identity Verification (Government ID)',
            'document_path' => 'verifications/marcus_gov_id.pdf',
            'status' => 'pending',
            'notes' => "[May 20, 2026 10:30] Initial submission received for review.\n[May 20, 2026 11:15] Government ID image scanned clearly.",
            'created_at' => now()->subDays(1),
            'updated_at' => now()->subDays(1),
        ]);

        EcommerceCustomerVerification::create([
            'user_id' => $sarah->id,
            'document_type' => 'Address Verification (Utility Bill)',
            'document_path' => 'verifications/sarah_utility.pdf',
            'status' => 'pending',
            'notes' => "[May 18, 2026 14:20] Utility bill dated within the last 60 days.",
            'created_at' => now()->subDays(3),
            'updated_at' => now()->subDays(3),
        ]);

        EcommerceCustomerVerification::create([
            'user_id' => $david->id,
            'document_type' => 'Payment Method Verification (Bank Statement)',
            'document_path' => 'verifications/david_bank.pdf',
            'status' => 'approved',
            'notes' => "[May 10, 2026 11:10] Bank statement uploaded.\n[May 10, 2026 11:45] Name matches account profile.\n[May 10, 2026 12:00] Verification approved by Administrator.",
            'created_at' => now()->subDays(10),
            'updated_at' => now()->subDays(10),
        ]);

        EcommerceCustomerVerification::create([
            'user_id' => $jessica->id,
            'document_type' => 'Identity Verification (Driver\'s License)',
            'document_path' => 'verifications/jessica_license.pdf',
            'status' => 'approved',
            'notes' => "[Apr 28, 2026 10:00] Driver's license verified.",
            'created_at' => now()->subDays(15),
            'updated_at' => now()->subDays(15),
        ]);

        EcommerceCustomerVerification::create([
            'user_id' => $robert->id,
            'document_type' => 'Address Verification (Utility Bill)',
            'document_path' => 'verifications/robert_bill.pdf',
            'status' => 'rejected',
            'notes' => "[Apr 15, 2026 16:30] Bill copy is blurry and address is cut off.\n[Apr 15, 2026 16:45] Rejected: Requested clear re-upload.",
            'created_at' => now()->subDays(20),
            'updated_at' => now()->subDays(20),
        ]);

        // 5. Seed Business Verification Submissions
        EcommerceCustomerVerification::create([
            'user_id' => $bizMarcus->id,
            'document_type' => 'Business License (SPDL-2026-4587)',
            'document_path' => 'verifications/stitchpro_license.pdf',
            'status' => 'pending',
            'notes' => "New business license & commercial incorporation filed.",
            'created_at' => now()->subHours(5),
            'updated_at' => now()->subHours(5),
        ]);

        EcommerceCustomerVerification::create([
            'user_id' => $bizSarah->id,
            'document_type' => 'Business License (EECO-2026-7891)',
            'document_path' => 'verifications/elite_license.pdf',
            'status' => 'pending',
            'notes' => "Awaiting verification of tax exemption ID.",
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        EcommerceCustomerVerification::create([
            'user_id' => $bizDavid->id,
            'document_type' => 'Commercial Registration (TPINC-2026-1562)',
            'document_path' => 'verifications/threadsprints_trade.pdf',
            'status' => 'approved',
            'notes' => "Verified through Corporate Registry.",
            'created_at' => now()->subDays(8),
            'updated_at' => now()->subDays(8),
        ]);

        EcommerceCustomerVerification::create([
            'user_id' => $bizJessica->id,
            'document_type' => 'Tax Certificate (CTL-2026-3478)',
            'document_path' => 'verifications/creativethreads_tax.pdf',
            'status' => 'approved',
            'notes' => "Corporate tax identification confirmed.",
            'created_at' => now()->subDays(14),
            'updated_at' => now()->subDays(14),
        ]);

        EcommerceCustomerVerification::create([
            'user_id' => $bizRobert->id,
            'document_type' => 'Business License (SRS-2026-9901)',
            'document_path' => 'verifications/sewnright_license.pdf',
            'status' => 'rejected',
            'notes' => "Expired business registration document uploaded.",
            'created_at' => now()->subDays(22),
            'updated_at' => now()->subDays(22),
        ]);
    }
}
