<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

try {
    $kernel->call('migrate');
    file_put_contents('c:\tmp\mig_error.txt', "SUCCESS\n" . $kernel->output());
} catch (\Throwable $e) {
    file_put_contents('c:\tmp\mig_error.txt', "ERROR:\n" . $e->getMessage());
}
