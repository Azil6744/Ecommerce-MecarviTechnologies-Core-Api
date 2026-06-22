<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Http\Controllers\Api\Admin\AdminAttributeController;
use Illuminate\Http\Request;

$controller = new AdminAttributeController();
$request = Request::create('/api/ecommerce/admin/attributes', 'POST', [
    'name' => 'Thread Type',
    'type' => 'Dropdown',
    'description' => 'Test Desc',
    'pricing_mode' => 'Per Item',
    'status' => 'Active',
    'values' => [
        [
            'name' => 'Polyester',
            'price' => 0.00,
            'pricing_mode' => 'Per Item',
            'status' => 'Active',
            'sort_order' => 1
        ]
    ]
]);

try {
    $response = $controller->store($request);
    echo "Response: " . $response->getContent() . PHP_EOL;
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . PHP_EOL;
    if ($e instanceof \Illuminate\Validation\ValidationException) {
        print_r($e->errors());
    }
}
