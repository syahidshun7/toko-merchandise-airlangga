<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class DemoCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $apparel = Category::query()->firstOrCreate(
            ['slug' => 'pakaian'],
            ['name' => 'Pakaian'],
        );

        $accessories = Category::query()->firstOrCreate(
            ['slug' => 'aksesori'],
            ['name' => 'Aksesori'],
        );

        $shirt = Product::withTrashed()->firstOrCreate(
            ['slug' => 'kaus-airlangga-demo'],
            [
                'category_id' => $apparel->id,
                'name' => 'Kaus Airlangga (Contoh)',
                'description' => 'Contoh merchandise pakaian dengan pilihan ukuran.',
                'base_price' => 85000,
                'is_featured' => true,
            ],
        );

        if ($shirt->wasRecentlyCreated) {
            $shirt->variants()->createMany([
                ['name' => 'M', 'stock' => 8],
                ['name' => 'L', 'stock' => 5],
                ['name' => 'XL', 'price' => 90000, 'stock' => 3],
            ]);
        }

        $bag = Product::withTrashed()->firstOrCreate(
            ['slug' => 'tas-kanvas-airlangga-demo'],
            [
                'category_id' => $accessories->id,
                'name' => 'Tas Kanvas Airlangga (Contoh)',
                'description' => 'Contoh merchandise dengan satu varian.',
                'base_price' => 45000,
            ],
        );

        if ($bag->wasRecentlyCreated) {
            $bag->variants()->create(['name' => 'Default', 'stock' => 10]);
        }
    }
}
