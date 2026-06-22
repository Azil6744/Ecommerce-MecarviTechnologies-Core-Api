<?php

namespace Tests\Feature;

use App\Models\ProductColor;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductColorControllerTest extends TestCase
{
    use DatabaseTransactions;

    private function adminHeaders(): array
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $admin->assignRole('super_admin');

        $token = $admin->createToken('feature-test')->plainTextToken;

        return [
            'Authorization' => 'Bearer ' . $token,
            'X-Central-Auth-Token' => $token,
            'Accept' => 'application/json',
        ];
    }

    public function test_index_returns_all_colors_sorted(): void
    {
        ProductColor::create([
            'name' => 'Teal',
            'slug' => 'teal',
            'hex_code' => '#008080',
            'sort_order' => 10,
            'is_active' => true,
        ]);

        ProductColor::create([
            'name' => 'Orange',
            'slug' => 'orange',
            'hex_code' => '#FFA500',
            'sort_order' => 5,
            'is_active' => true,
        ]);

        $response = $this
            ->withHeaders($this->adminHeaders())
            ->getJson('/api/v1/product-colors');

        $response->assertOk();
        
        $data = $response->json();
        $this->assertGreaterThanOrEqual(2, count($data));
        
        // Assert sorting order (Orange with 5 should come before Teal with 10)
        $orangeIndex = -1;
        $tealIndex = -1;
        foreach ($data as $index => $color) {
            if ($color['name'] === 'Orange') {
                $orangeIndex = $index;
            } elseif ($color['name'] === 'Teal') {
                $tealIndex = $index;
            }
        }
        
        $this->assertNotEquals(-1, $orangeIndex);
        $this->assertNotEquals(-1, $tealIndex);
        $this->assertLessThan($tealIndex, $orangeIndex);
    }

    public function test_store_creates_color_with_swatch_image(): void
    {
        Storage::fake('public');

        $file = UploadedFile::fake()->create('swatch.png', 10, 'image/png');

        $response = $this
            ->withHeaders($this->adminHeaders())
            ->postJson('/api/v1/product-colors', [
                'name' => 'Gold',
                'hex_code' => '#FFD700',
                'sort_order' => 1,
                'is_active' => true,
                'swatch_image' => $file,
            ]);

        $response->assertCreated();
        $response->assertJsonPath('name', 'Gold');
        $response->assertJsonPath('hex_code', '#FFD700');
        $this->assertNotNull($response->json('swatch_image'));

        $this->assertDatabaseHas('product_colors', [
            'name' => 'Gold',
            'hex_code' => '#FFD700',
        ]);

        Storage::disk('public')->assertExists($response->json('swatch_image'));
    }

    public function test_update_modifies_color_and_handles_image_removal(): void
    {
        Storage::fake('public');

        $color = ProductColor::create([
            'name' => 'Silver',
            'slug' => 'silver',
            'hex_code' => '#C0C0C0',
            'sort_order' => 1,
            'is_active' => true,
            'swatch_image' => 'product-colors/silver.png',
        ]);

        Storage::disk('public')->put('product-colors/silver.png', 'content');

        // Test normal update
        $response = $this
            ->withHeaders($this->adminHeaders())
            ->putJson("/api/v1/product-colors/{$color->id}", [
                'name' => 'Silver Updated',
                'hex_code' => '#CCCCCC',
                'sort_order' => 2,
                'is_active' => false,
            ]);

        $response->assertOk();
        $response->assertJsonPath('name', 'Silver Updated');
        $response->assertJsonPath('hex_code', '#CCCCCC');
        $response->assertJsonPath('sort_order', 2);
        $response->assertJsonPath('is_active', false);

        // Test update with swatch removal
        $response = $this
            ->withHeaders($this->adminHeaders())
            ->putJson("/api/v1/product-colors/{$color->id}", [
                'name' => 'Silver Updated',
                'hex_code' => '#CCCCCC',
                'remove_swatch_image' => true,
            ]);

        $response->assertOk();
        $this->assertNull($response->json('swatch_image'));
        Storage::disk('public')->assertMissing('product-colors/silver.png');
    }

    public function test_destroy_deletes_color_and_associated_image(): void
    {
        Storage::fake('public');

        $color = ProductColor::create([
            'name' => 'Bronze',
            'slug' => 'bronze',
            'hex_code' => '#CD7F32',
            'sort_order' => 1,
            'is_active' => true,
            'swatch_image' => 'product-colors/bronze.png',
        ]);

        Storage::disk('public')->put('product-colors/bronze.png', 'content');

        $response = $this
            ->withHeaders($this->adminHeaders())
            ->deleteJson("/api/v1/product-colors/{$color->id}");

        $response->assertOk();
        
        $this->assertDatabaseMissing('product_colors', [
            'id' => $color->id,
        ]);

        Storage::disk('public')->assertMissing('product-colors/bronze.png');
    }
}
