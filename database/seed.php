<?php

$user = \App\Models\User::first();
if (!$user) {
    $user = \App\Models\User::create([
        'name' => 'Demo User',
        'email' => 'demo@example.com',
        'password' => bcrypt('password'),
        'role' => 'user'
    ]);
}

\App\Models\EcommerceCustomerVerification::create([
    'user_id' => $user->id,
    'document_type' => 'Business License',
    'document_path' => '/placeholders/doc.pdf',
    'status' => 'pending',
    'created_at' => now()->subDays(2)
]);

\App\Models\EcommerceCustomerVerification::create([
    'user_id' => $user->id,
    'document_type' => 'Tax Certificate',
    'document_path' => '/placeholders/doc.pdf',
    'status' => 'approved',
    'created_at' => now()->subDays(3)
]);

\App\Models\EcommerceCustomerVerification::create([
    'user_id' => $user->id,
    'document_type' => 'ID Card',
    'document_path' => '/placeholders/doc.pdf',
    'status' => 'rejected',
    'notes' => 'Image is too blurry to read.',
    'created_at' => now()->subDays(4)
]);

echo "Seeded successfully.\n";
