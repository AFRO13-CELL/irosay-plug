<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Starter catalogue exactly as given in the spec (sections 12-15).
     * These are PRODUCT-level entries (a "model", e.g. "iPhone 13 Pro Max
     * 256GB"). For the iPhones category (serialized), stock_quantity stays
     * 0 here on purpose — individual IMEI-tracked units get created in
     * Phase 5 (Purchases) when real stock is received, never seeded as
     * anonymous quantity. Prices are starter values and fully editable.
     */
    public function run(): void
    {
        $iphones      = Category::where('slug', 'iphones')->firstOrFail();
        $watches      = Category::where('slug', 'apple-watches')->firstOrFail();
        $ipads        = Category::where('slug', 'ipads')->firstOrFail();
        $airpods      = Category::where('slug', 'airpods')->firstOrFail();
        $chargers     = Category::where('slug', 'chargers')->firstOrFail();
        $networking   = Category::where('slug', 'networking')->firstOrFail();
        $otherAcc     = Category::where('slug', 'other-accessories')->firstOrFail();

        // --- iPhones (section 12) — serialized, no stock seeded yet ---
        $iphonePrices = [
            'iPhone 11 64GB' => 1950,
            'iPhone 11 128GB' => 2250,
            'iPhone 11 Pro 64GB' => 2450,
            'iPhone 11 Pro 512GB' => 2700,
            'iPhone 11 Pro Max 64GB' => 2500,
            'iPhone 12 Mini 64GB' => 1800,
            'iPhone 12 Mini 128GB' => 2000,
            'iPhone 12 64GB' => 2150,
            'iPhone 12 128GB' => 2600,
            'iPhone 12 Pro 128GB' => 3150,
            'iPhone 12 Pro 256GB' => 3300,
            'iPhone 12 Pro Max 128GB' => 3750,
            'iPhone 13 Mini 128GB' => 2600,
            'iPhone 13 256GB' => 3500,
            'iPhone 13 Pro Max 128GB' => 4900,
            'iPhone 13 Pro Max 256GB' => 5600,
            'iPhone 13 Pro Max 512GB' => 5800,
            'iPhone 14 128GB' => 3600,
            'iPhone 14 Plus 128GB' => 4300,
            'iPhone 14 Pro 128GB' => 5700,
            'iPhone 14 Pro 256GB' => 5900,
            'iPhone 14 Pro Max 128GB' => 6400,
            'iPhone 14 Pro Max 256GB' => 6900,
            'iPhone 15 128GB' => 5200,
            'iPhone 15 Plus 128GB' => 6200,
            'iPhone 15 Pro 128GB' => 6700,
            'iPhone 15 Pro Max 256GB' => 8300,
            'iPhone 16 Pro 256GB' => 9350,
            'iPhone 16 Pro Max 256GB' => 10900,
            'iPhone 16 Plus 128GB' => 8400,
            'iPhone 17 256GB' => 9750,
            'iPhone 17 Air 256GB' => 9400,
            'iPhone 17 Pro 256GB eSIM' => 12000,
            'iPhone 17 Pro Max 256GB eSIM' => 13500,
            'iPhone 17 Pro 256GB' => 13500,
            'iPhone 17 Pro Max 256GB' => 15500,
            'iPhone 17 Pro Max 512GB' => 17100,
            'iPhone 17 Pro 256GB Non-Active' => 14400,
            'iPhone 17 Pro Max 256GB Non-Active' => 16000,
            'iPhone 17 Pro Max 256GB Non-Active Blue' => 16000,
            'iPhone 17 Pro Max 256GB Non-Active Orange' => 16000,
        ];

        foreach ($iphonePrices as $name => $price) {
            $this->upsert($iphones, $name, $price, 'serialized', 'Apple');
        }

        // --- iPad (section 13) ---
        $this->upsert($ipads, 'iPad 11th Gen (A16)', 5000, 'quantity', 'Apple');

        // --- Apple Watches (section 14) ---
        $watchPrices = [
            'Apple Watch Series 10 42mm' => 4000,
            'Apple Watch Series 9 45mm' => 3650,
            'Apple Watch Series 8 45mm' => 2800,
            'Apple Watch Series 8 41mm' => 2400,
            'Apple Watch Series 7 45mm' => 2400,
            'Apple Watch Series 6 44mm' => 1800,
            'Apple Watch Series 6 40mm' => 1700,
            'Apple Watch Series 5 44mm' => 1600,
            'Apple Watch Series 5 40mm' => 1500,
            'Apple Watch Series 4 44mm' => 1350,
            'Apple Watch Series 4 40mm' => 1250,
            'Apple Watch Series 3 42mm' => 1100,
            'Apple Watch Series 3 38mm' => 950,
        ];

        foreach ($watchPrices as $name => $price) {
            $this->upsert($watches, $name, $price, 'quantity', 'Apple');
        }

        // --- Accessories (section 15) ---
        $this->upsert($chargers, 'Magnetic Series Charger', 80, 'quantity');
        $this->upsert($chargers, 'Original Apple Lightning Charger', 40, 'quantity', 'Apple');
        $this->upsert($airpods, 'AirPods Pro 2nd Generation with Active Noise Cancellation', 100, 'quantity', 'Apple');
        $this->upsert($airpods, 'AirPods 4 with Active Noise Cancellation', 200, 'quantity', 'Apple');
        $this->upsert($networking, 'CAT 4 Router', 450, 'quantity');
        // Spec lists "Pocket MiFi" twice at two different prices (250 and 300) —
        // kept as two distinct SKUs rather than silently dropping one.
        $this->upsert($networking, 'Pocket MiFi (Standard)', 250, 'quantity');
        $this->upsert($networking, 'Pocket MiFi (Premium)', 300, 'quantity');
        $this->upsert($otherAcc, 'Veyes Reed Diffuser', 50, 'quantity');
    }

    private function upsert(Category $category, string $name, float $sellingPrice, string $trackingType, ?string $brand = null): void
    {
        Product::updateOrCreate(
            ['sku' => Str::slug($name)],
            [
                'category_id' => $category->id,
                'name' => $name,
                'brand' => $brand,
                'tracking_type' => $trackingType,
                'buying_price' => 0,       // set per-unit at purchase time; this is just a placeholder reference
                'selling_price' => $sellingPrice,
                'stock_quantity' => 0,     // real stock comes in via Phase 5 Purchases, not seeded
                'min_stock_level' => $trackingType === 'quantity' ? 5 : 0,
                'status' => 'active',
            ]
        );
    }
}
