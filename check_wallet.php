<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\DB;

$user = User::where('email', 'developmentwithazil@gmail.com')->first();
echo "LOCAL USER: {$user->name} ({$user->email}, ID: {$user->id})\n";
echo "LOCAL WALLET BALANCE: $" . number_format($user->wallet_balance ?? 0, 2) . "\n\n";

try {
    if (config('database.connections.central_auth')) {
        $cUser = DB::connection('central_auth')->table('users')->whereRaw('LOWER(email) = ?', [strtolower($user->email)])->first();
        if ($cUser) {
            $cWallet = DB::connection('central_auth')->table('central_wallets')->where('user_id', $cUser->id)->first();
            echo "CENTRAL USER ID: {$cUser->id}\n";
            echo "CENTRAL WALLET BALANCE: $" . number_format($cWallet->balance ?? 0, 2) . "\n";
        } else {
            echo "CENTRAL USER NOT FOUND\n";
        }
    }
} catch (\Throwable $e) {
    echo "CENTRAL DB ERROR: " . $e->getMessage() . "\n";
}
