<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

class DemoMerchandiseMediaSeeder extends Seeder
{
    public function run(): void
    {
        $disk = Storage::disk('public');

        foreach (['kaus-airlangga-demo' => 'kaus', 'tas-kanvas-airlangga-demo' => 'tas'] as $slug => $asset) {
            $product = Product::where('slug', $slug)->first();
            if ($product && ! $product->images()->exists()) {
                $path = "products/demo-{$asset}-ai.webp";
                $disk->put($path, file_get_contents(public_path("images/demo/{$asset}.webp")));
                $product->images()->create(['path' => $path, 'is_primary' => true, 'sort_order' => 0]);
            }
        }

        $path = 'banners/demo-promosi-ai.webp';
        if (! Banner::where('image_path', $path)->exists()) {
            $disk->put($path, file_get_contents(public_path('images/demo/banner-promosi.webp')));
            Banner::create([
                'title' => 'Jelajahi koleksi sekolah',
                'description' => 'Temukan kaus dan tas kanvas di katalog. Gambar banner berupa ilustrasi.',
                'image_path' => $path,
                'sort_order' => 0,
                'is_active' => true,
            ]);
        }
    }
}
