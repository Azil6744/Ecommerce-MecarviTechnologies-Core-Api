<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\EcommerceCoupon;
use App\Models\EcommerceReview;
use App\Models\Product;
use App\Models\ProductCustomizationOption;
use App\Models\ProductPreviewAsset;
use App\Models\ProductPricingRule;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use RuntimeException;

class DemoProductSeeder extends Seeder
{
    private const IMAGE_WIDTH = 1200;
    private const IMAGE_HEIGHT = 900;

    public function run(): void
    {
        $categories = $this->seedCategories();

        $products = [
            [
                'category' => 'Polos',
                'name' => 'Classic Cotton Embroidered Polo',
                'sku' => 'DEMO-POLO-001',
                'price' => 34.99,
                'sale_price' => 29.99,
                'colors' => [[12, 49, 86], [255, 15, 109], [245, 248, 252]],
                'tags' => ['polo', 'uniform', 'embroidery', 'business'],
                'quantity' => 48,
            ],
            [
                'category' => 'Hoodies',
                'name' => 'Premium Logo Embroidered Hoodie',
                'sku' => 'DEMO-HOODIE-002',
                'price' => 58.00,
                'sale_price' => 49.50,
                'colors' => [[17, 24, 39], [217, 4, 41], [229, 231, 235]],
                'tags' => ['hoodie', 'team apparel', 'custom logo'],
                'quantity' => 36,
            ],
            [
                'category' => 'Headwear',
                'name' => 'Structured Embroidered Cap',
                'sku' => 'DEMO-CAP-003',
                'price' => 24.00,
                'sale_price' => 19.99,
                'colors' => [[6, 95, 70], [250, 204, 21], [236, 253, 245]],
                'tags' => ['cap', 'hat', 'headwear', 'logo'],
                'quantity' => 72,
            ],
            [
                'category' => 'Jackets',
                'name' => 'Corporate Softshell Embroidered Jacket',
                'sku' => 'DEMO-JACKET-004',
                'price' => 88.00,
                'sale_price' => 76.00,
                'colors' => [[30, 41, 59], [59, 130, 246], [241, 245, 249]],
                'tags' => ['jacket', 'corporate', 'outerwear'],
                'quantity' => 24,
            ],
            [
                'category' => 'Bags',
                'name' => 'Custom Embroidered Canvas Tote',
                'sku' => 'DEMO-TOTE-005',
                'price' => 27.50,
                'sale_price' => 22.00,
                'colors' => [[120, 53, 15], [234, 88, 12], [255, 247, 237]],
                'tags' => ['bag', 'tote', 'promotional', 'canvas'],
                'quantity' => 100,
            ],
            [
                'category' => 'Workwear',
                'name' => 'Heavy Duty Embroidered Work Shirt',
                'sku' => 'DEMO-WORK-006',
                'price' => 42.00,
                'sale_price' => 37.00,
                'colors' => [[71, 85, 105], [14, 165, 233], [248, 250, 252]],
                'tags' => ['workwear', 'staff shirt', 'durable'],
                'quantity' => 60,
            ],
        ];

        $seededProducts = collect();

        foreach ($products as $index => $demo) {
            $images = $this->generateProductImages($demo['sku'], $demo['colors'], $index);

            $product = Product::updateOrCreate(
                ['sku' => $demo['sku']],
                [
                    'name' => $demo['name'],
                    'description' => $this->descriptionFor($demo['name']),
                    'short_description' => 'Demo storefront product with generated gallery images for local testing.',
                    'price' => $demo['price'],
                    'sale_price' => $demo['sale_price'],
                    'cost_price' => round($demo['price'] * 0.45, 2),
                    'category_id' => $categories[$demo['category']]->id,
                    'stock_quantity' => $demo['quantity'],
                    'low_stock_threshold' => 10,
                    'weight' => 1.25 + ($index * 0.15),
                    'dimensions' => '12 x 10 x 2 in',
                    'images' => $images,
                    'is_active' => true,
                    'is_featured' => $index < 4,
                    'is_digital' => false,
                    'download_url' => null,
                    'seo_title' => $demo['name'],
                    'seo_description' => 'Demo product seeded for Mecarvi Embroidery storefront testing.',
                    'tags' => $demo['tags'],
                    'attributes' => $this->attributesFor($demo),
                    'variants' => [
                        ['name' => 'Small Batch', 'min_quantity' => 12, 'price' => $demo['price']],
                        ['name' => 'Team Pack', 'min_quantity' => 48, 'price' => $demo['sale_price']],
                    ],
                ]
            );

            $this->seedReviews($product);
            $this->seedPreviewAssets($product, $images);
            $this->seedCustomizationOptions($product);
            $this->seedPricingRules($product);
            $seededProducts->push($product);
        }

        $this->seedProductRelations($seededProducts);
        $this->seedCoupons($seededProducts);
    }

    private function seedCategories(): array
    {
        $hasParentId = Schema::hasColumn('categories', 'parent_id');
        $hasSortOrder = Schema::hasColumn('categories', 'sort_order');

        $rootData = [
            'name' => 'Demo Embroidered Products',
            'description' => 'Seeded demo categories for storefront catalog testing.',
            'is_active' => true,
        ];

        if ($hasSortOrder) {
            $rootData['sort_order'] = 1;
        }

        $root = Category::updateOrCreate(
            ['slug' => 'demo-embroidered-products'],
            $rootData
        );

        $names = ['Polos', 'Hoodies', 'Headwear', 'Jackets', 'Bags', 'Workwear'];
        $categories = [];

        foreach ($names as $index => $name) {
            $categoryData = [
                'name' => $name,
                'description' => "Demo {$name} products.",
                'is_active' => true,
            ];

            if ($hasParentId) {
                $categoryData['parent_id'] = $root->id;
            }

            if ($hasSortOrder) {
                $categoryData['sort_order'] = $index + 1;
            }

            $categories[$name] = Category::updateOrCreate(
                ['slug' => 'demo-' . Str::slug($name)],
                $categoryData
            );
        }

        return $categories;
    }

    private function generateProductImages(string $sku, array $colors, int $seed): array
    {
        $paths = [];
        $safeSku = Str::slug(strtolower($sku));

        for ($i = 1; $i <= 4; $i++) {
            $relativePath = "products/demo/{$safeSku}-gallery-{$i}.png";
            $storagePath = storage_path("app/public/{$relativePath}");

            File::ensureDirectoryExists(dirname($storagePath));

            $png = $this->makePng($colors, $seed + $i, $i);
            if (File::put($storagePath, $png) === false) {
                $this->command?->warn("Unable to write demo product image at {$storagePath}. Check storage/app/public permissions.");
            } else {
                $this->mirrorImageToPublicStorage($relativePath, $png);
            }

            $paths[] = $relativePath;
        }

        return $paths;
    }

    private function mirrorImageToPublicStorage(string $relativePath, string $png): void
    {
        $publicStorageRoot = public_path('storage');

        if (! File::exists($publicStorageRoot) && ! is_link($publicStorageRoot)) {
            $this->command?->warn('Skipped public/storage image mirror because the storage link does not exist. Run: php artisan storage:link');
            return;
        }

        $publicPath = public_path("storage/{$relativePath}");

        try {
            File::ensureDirectoryExists(dirname($publicPath));

            if (File::put($publicPath, $png) === false) {
                $this->command?->warn("Could not mirror demo image to {$publicPath}. Check public/storage permissions.");
            }
        } catch (\Throwable $exception) {
            $this->command?->warn("Could not mirror demo image to public/storage: {$exception->getMessage()}");
        }
    }

    private function makePng(array $colors, int $seed, int $variant): string
    {
        [$primary, $accent, $background] = $colors;
        $width = self::IMAGE_WIDTH;
        $height = self::IMAGE_HEIGHT;
        $raw = '';

        for ($y = 0; $y < $height; $y++) {
            $raw .= "\x00";
            for ($x = 0; $x < $width; $x++) {
                $t = ($x / max(1, $width - 1) + $y / max(1, $height - 1)) / 2;
                $stripe = ((int) floor(($x + $y + $seed * 37) / (28 + $variant * 4))) % 2 === 0 ? 1 : 0;
                $stitch = (abs(($x % 42) - 21) < 2 || abs(($y % 36) - 18) < 2) ? 1 : 0;
                $cx = $width * (0.42 + $variant * 0.035);
                $cy = $height * 0.48;
                $dx = ($x - $cx) / ($width * 0.29);
                $dy = ($y - $cy) / ($height * 0.34);
                $insidePatch = ($dx * $dx + $dy * $dy) < 1;
                $insideLogo = abs($x - $cx) < $width * 0.12 && abs($y - $cy) < $height * 0.08;

                $rgb = $this->mix($background, $primary, $t * 0.45);
                if ($stripe) {
                    $rgb = $this->mix($rgb, $primary, 0.10);
                }
                if ($insidePatch) {
                    $rgb = $this->mix($primary, $accent, (($x + $seed) % 180) / 520);
                }
                if ($insideLogo) {
                    $rgb = $this->mix($accent, [255, 255, 255], 0.18);
                }
                if ($stitch) {
                    $rgb = $this->mix($rgb, [255, 255, 255], 0.18);
                }

                $raw .= chr($rgb[0]) . chr($rgb[1]) . chr($rgb[2]);
            }
        }

        return $this->pngFromRawRgb($width, $height, $raw);
    }

    private function pngFromRawRgb(int $width, int $height, string $raw): string
    {
        $signature = "\x89PNG\r\n\x1a\n";
        $ihdr = pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0);

        return $signature
            . $this->pngChunk('IHDR', $ihdr)
            . $this->pngChunk('IDAT', gzcompress($raw, 6))
            . $this->pngChunk('IEND', '');
    }

    private function pngChunk(string $type, string $data): string
    {
        return pack('N', strlen($data))
            . $type
            . $data
            . pack('N', crc32($type . $data));
    }

    private function mix(array $a, array $b, float $amount): array
    {
        $amount = max(0, min(1, $amount));

        return [
            (int) round($a[0] + (($b[0] - $a[0]) * $amount)),
            (int) round($a[1] + (($b[1] - $a[1]) * $amount)),
            (int) round($a[2] + (($b[2] - $a[2]) * $amount)),
        ];
    }

    private function descriptionFor(string $name): string
    {
        return "{$name} is a seeded demo product for testing the live storefront catalog, image gallery, product details, related products, pricing, and order flow.";
    }

    private function attributesFor(array $demo): array
    {
        return [
            'product_type' => 'physical',
            'brand_name' => 'Mecarvi Demo',
            'product_label' => 'Seeded Product',
            'product_labels' => ['T-Shirts', 'Hoodies', 'Polos', 'Hats', 'Jackets', 'Workwear'],
            'color_options' => ['Navy', 'Black', 'White', 'Red'],
            'print_attributes' => ['Embroidery', 'Logo placement', 'Thread color matching'],
            'delivery_time_label' => '3-5 business days',
            'hero_label' => 'Demo Embroidery Product',
            'rating_value' => '4.8',
            'review_count' => '24',
            'feature_labels' => ['Premium Quality', 'Fast Turnaround', 'No Setup Fees'],
            'highlights' => [
                ['title' => 'Expert Embroidery', 'description' => 'Precision stitching for the best finish.'],
                ['title' => 'Fast Turnaround', 'description' => '3-5 business days standard delivery.'],
                ['title' => 'No Setup Fees', 'description' => 'Always free setup on every order.'],
            ],
            'promo_badges' => ['Demo item', 'Gallery ready'],
            'promo_contact_text' => 'Contact our team for custom embroidery options.',
            'why_order_title' => 'Why Order This Demo Product?',
            'help_section_title' => 'Need Help Choosing?',
            'help_cards' => [
                ['title' => 'Call Us', 'text' => '(877) 853-3484'],
                ['title' => 'Email Us', 'text' => 'contact@mecarviembroidery.com'],
                ['title' => 'Live Chat', 'text' => 'Chat with our team'],
                ['title' => 'Support Ticket', 'text' => 'Submit a ticket'],
            ],
            'customization_title' => 'Customize Your Embroidery - 6 Simple Steps',
            'customization_steps' => ['Choose Product', 'Embroidery Type', 'Placement', 'Size', 'Thread Colors', 'Quantity'],
            'select_product_title' => 'Select Product',
            'live_preview_title' => 'Live Preview',
            'summary_title' => 'Your Customization Summary',
            'delivery_title' => 'Estimated Delivery',
            'delivery_window' => 'May 24 - May 30',
            'delivery_note' => 'For standard demo production.',
            'upload_help_title' => 'Upload Logo',
            'upload_help_text' => 'JPG, PNG, PDF, AI, EPS (Max 10MB)',
            'embroidery_type' => 'Embroidery',
            'placement' => 'Left Chest',
            'size_label' => 'Standard (4" Wide)',
            'quantity_label' => 'Pieces',
            'quantity' => (string) $demo['quantity'],
            'thread_colors' => ['#0c3156', '#ff0f6d', '#ffffff'],
            'primary_cta_label' => 'Start Your Order',
            'secondary_cta_label' => 'Request Quote',
            'preview_note' => 'Get a digital preview within hours',
            'process_title' => 'Our Embroidery Process',
            'process_steps' => [
                ['title' => 'Design Review', 'description' => 'We review your logo and product details.'],
                ['title' => 'Digitizing', 'description' => 'We prepare a stitch-ready file.'],
                ['title' => 'Stitch Production', 'description' => 'Your demo product moves through production.'],
                ['title' => 'Quality Check', 'description' => 'We inspect the finished embroidery.'],
                ['title' => 'Delivery', 'description' => 'Packed and ready for shipment.'],
            ],
            'reviews_title' => 'Customer Reviews',
            'special_offers_title' => 'Special Offers',
            'offer_cards' => [
                ['title' => '10% OFF', 'subtitle' => 'ON ALL ORDERS', 'code' => 'MECARVI10', 'note' => 'Valid till: May 31, 2025', 'side' => 'pink'],
                ['title' => 'GBP15 OFF', 'subtitle' => 'ORDERS OVER GBP150', 'code' => 'SAVE15', 'note' => 'Valid till: May 31, 2025', 'side' => 'blue'],
            ],
            'recent_work_title' => 'Our Recent Work',
            'recent_work_cta_label' => 'View More Projects ->',
            'featured_products_title' => 'Featured Embroidery Products',
            'final_cta_title' => 'Ready to Start Your Custom Embroidery Order?',
            'final_cta_text' => 'Upload your logo, choose your options and get a digital preview within hours.',
            'upload_design_label' => 'Upload Design',
        ];
    }

    private function seedReviews(Product $product): void
    {
        $reviews = [
            ['customer_name' => 'James D.', 'rating' => 5, 'title' => 'Excellent quality', 'comment' => 'The stitching looks clean and the product gallery works nicely.'],
            ['customer_name' => 'Sarah R.', 'rating' => 5, 'title' => 'Great demo item', 'comment' => 'Perfect sample product for testing the storefront flow.'],
            ['customer_name' => 'Maya K.', 'rating' => 4, 'title' => 'Looks professional', 'comment' => 'The product details and images are easy to review.'],
        ];

        foreach ($reviews as $review) {
            EcommerceReview::updateOrCreate(
                [
                    'product_id' => (string) $product->id,
                    'customer_name' => $review['customer_name'],
                ],
                [
                    'rating' => $review['rating'],
                    'title' => $review['title'],
                    'comment' => $review['comment'],
                    'status' => 'Approved',
                ]
            );
        }
    }

    private function seedPreviewAssets(Product $product, array $images): void
    {
        $sides = ['front', 'back', 'left', 'right'];

        foreach ($images as $index => $image) {
            ProductPreviewAsset::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'side' => $sides[$index] ?? 'front',
                    'sort_order' => $index,
                ],
                [
                    'image_path' => $image,
                    'is_active' => true,
                    'metadata' => ['label' => ucfirst($sides[$index] ?? 'front')],
                ]
            );
        }
    }

    private function seedCustomizationOptions(Product $product): void
    {
        $options = [
            ['embroidery_type', 'embroidery', 'Embroidery', 0],
            ['embroidery_type', 'premium_embroidery', 'Premium Embroidery', 4],
            ['placement', 'left_chest', 'Left Chest', 0],
            ['placement', 'full_back', 'Full Back', 8],
            ['size', 'standard_4_wide', 'Standard (4" Wide)', 0],
            ['size', 'large_8_wide', 'Large (8" Wide)', 6],
            ['thread_colors', 'three_colors', '3 Colors', 0],
            ['thread_colors', 'six_colors', '6 Colors', 5],
        ];

        foreach ($options as $index => [$type, $key, $label, $modifier]) {
            ProductCustomizationOption::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'option_type' => $type,
                    'option_key' => $key,
                ],
                [
                    'label' => $label,
                    'price_modifier' => $modifier,
                    'sort_order' => $index,
                    'is_active' => true,
                    'metadata' => [],
                ]
            );
        }
    }

    private function seedPricingRules(Product $product): void
    {
        $rules = [
            ['min' => 50, 'max' => 99, 'type' => 'percentage', 'value' => -5],
            ['min' => 100, 'max' => null, 'type' => 'percentage', 'value' => -10],
        ];

        foreach ($rules as $index => $rule) {
            ProductPricingRule::updateOrCreate(
                [
                    'product_id' => $product->id,
                    'rule_type' => 'quantity',
                    'min_quantity' => $rule['min'],
                    'max_quantity' => $rule['max'],
                ],
                [
                    'adjustment_type' => $rule['type'],
                    'adjustment_value' => $rule['value'],
                    'sort_order' => $index,
                    'is_active' => true,
                    'metadata' => ['label' => "{$rule['value']}% bulk adjustment"],
                ]
            );
        }
    }

    private function seedProductRelations($products): void
    {
        $ids = $products->pluck('id')->values();

        foreach ($products as $product) {
            $relatedIds = $ids->filter(fn ($id) => $id !== $product->id)->values();

            foreach (['related', 'recent_work', 'featured'] as $type) {
                foreach ($relatedIds->take($type === 'featured' ? 8 : 5) as $index => $relatedId) {
                    DB::table('product_related_products')->updateOrInsert(
                        [
                            'product_id' => $product->id,
                            'related_product_id' => $relatedId,
                            'relation_type' => $type,
                        ],
                        [
                            'relation_type' => $type,
                            'sort_order' => $index,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]
                    );
                }
            }
        }
    }

    private function seedCoupons($products): void
    {
        $mecarvi10 = EcommerceCoupon::updateOrCreate(
            ['code' => 'MECARVI10'],
            [
                'title' => '10% OFF',
                'subtitle' => 'ON ALL ORDERS',
                'discount_type' => 'percentage',
                'discount_value' => 10,
                'min_order_amount' => 0,
                'is_active' => true,
                'metadata' => ['side' => 'pink', 'note' => 'Valid demo coupon'],
            ]
        );

        $save15 = EcommerceCoupon::updateOrCreate(
            ['code' => 'SAVE15'],
            [
                'title' => 'GBP15 OFF',
                'subtitle' => 'ORDERS OVER GBP150',
                'discount_type' => 'fixed',
                'discount_value' => 15,
                'min_order_amount' => 150,
                'is_active' => true,
                'metadata' => ['side' => 'blue', 'note' => 'Valid demo coupon'],
            ]
        );

        foreach ($products as $product) {
            $product->coupons()->syncWithoutDetaching([$mecarvi10->id, $save15->id]);
        }
    }
}
