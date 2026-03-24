<?php

$models = ['Order', 'Quotation', 'Membership', 'WalletTransaction', 'Dispute', 'Ticket', 'Return', 'GiftCard', 'Affiliate', 'Review'];

foreach($models as $model) {
    if ($model === 'Return') {
        $className = 'EcommerceReturn';
    } else {
        $className = 'Ecommerce' . $model;
    }
    
    $filePath = __DIR__.'/app/Http/Controllers/Api/Ecommerce/'.$className.'Controller.php';

    $content = "<?php\n\nnamespace App\Http\Controllers\Api\Ecommerce;\n\nuse App\Http\Controllers\Controller;\nuse Illuminate\Http\Request;\nuse App\Models\\$className;\nuse Illuminate\Support\Facades\Schema;\n\nclass {$className}Controller extends Controller\n{\n    public function index(Request \$request)\n    {\n        \$user = \$request->user();\n        // Check if admin to return all, or just user\n        if (\$user && \$user->isSuperAdmin()) {\n            return response()->json(['success' => true, 'data' => {$className}::all()]);\n        }\n        \n        // Get by user_id if column exists, otherwise all\n        if(Schema::hasColumn((new {$className})->getTable(), 'user_id')) {\n            \$query = {$className}::where('user_id', \$user->id);\n            return response()->json(['success' => true, 'data' => \$query->get()]);\n        }\n\n        return response()->json(['success' => true, 'data' => {$className}::all()]);\n    }\n\n    public function store(Request \$request)\n    {\n        \$data = \$request->all();\n        if(Schema::hasColumn((new {$className})->getTable(), 'user_id')) {\n            \$data['user_id'] = \$request->user()->id;\n        }\n        \$item = {$className}::create(\$data);\n        return response()->json(['success' => true, 'data' => \$item]);\n    }\n\n    public function show(Request \$request, \$id)\n    {\n        \$item = {$className}::findOrFail(\$id);\n        return response()->json(['success' => true, 'data' => \$item]);\n    }\n\n    public function update(Request \$request, \$id)\n    {\n        \$item = {$className}::findOrFail(\$id);\n        \$item->update(\$request->all());\n        return response()->json(['success' => true, 'data' => \$item]);\n    }\n\n    public function destroy(Request \$request, \$id)\n    {\n        \$item = {$className}::findOrFail(\$id);\n        \$item->delete();\n        return response()->json(['success' => true, 'message' => 'Deleted successfully']);\n    }\n}\n";

    file_put_contents($filePath, $content);
    echo "Generated " . $className . "Controller\n";
}
