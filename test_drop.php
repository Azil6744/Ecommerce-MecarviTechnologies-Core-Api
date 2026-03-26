<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

Schema::disableForeignKeyConstraints();
Schema::dropIfExists('ecommerce_cart_items');
Schema::dropIfExists('ecommerce_carts');
Schema::dropIfExists('ecommerce_addresses');
Schema::enableForeignKeyConstraints();

DB::table('migrations')->where('migration', 'like', '%ecommerce_cart%')->delete();
DB::table('migrations')->where('migration', 'like', '%ecommerce_address%')->delete();

echo "Dropped tables and migrations records.\n";
