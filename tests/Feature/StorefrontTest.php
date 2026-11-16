<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorefrontTest extends TestCase
{
    use RefreshDatabase;

    public function test_inactive_and_archived_products_are_hidden_from_public_routes(): void
    {
        $this->withoutVite();
        $active = Category::create(['name' => 'Pakaian', 'slug' => 'pakaian', 'is_active' => true]);
        $inactive = Category::create(['name' => 'Arsip', 'slug' => 'arsip', 'is_active' => false]);
        $visible = $this->product($active, 'kaus-publik', 50000);
        $related = $this->product($active, 'kaus-terkait', 60000);
        $hidden = $this->product($active, 'kaus-nonaktif', 70000);
        $hidden->update(['is_active' => false]);
        $archived = $this->product($active, 'kaus-arsip', 80000);
        $archived->delete();
        $hiddenCategory = $this->product($inactive, 'kaus-kategori-arsip', 90000);

        $this->get(route('store.home'))->assertOk()->assertSee($visible->name)->assertDontSee($hidden->name);
        $this->get(route('store.catalog'))->assertOk()->assertSee($visible->name)
            ->assertDontSee($hidden->name)->assertDontSee($archived->name)->assertDontSee($hiddenCategory->name);
        $this->get(route('store.product', $visible->slug))->assertOk()->assertSee($related->name)->assertDontSee($hidden->name);
        $this->get(route('store.category', $inactive->slug))->assertNotFound();

        foreach ([$hidden, $archived, $hiddenCategory] as $product) {
            $this->get(route('store.product', $product->slug))->assertNotFound();
        }
    }

    public function test_search_category_and_effective_price_sort_work_together(): void
    {
        $this->withoutVite();
        $clothes = Category::create(['name' => 'Pakaian', 'slug' => 'pakaian']);
        $accessories = Category::create(['name' => 'Aksesori', 'slug' => 'aksesori']);
        $cheap = $this->product($clothes, 'kaus-ekonomis', 100000, 35000);
        $expensive = $this->product($clothes, 'kaus-premium', 75000);
        $this->product($accessories, 'kaus-aksesori', 15000);
        $this->product($clothes, 'jaket', 25000);

        $filters = ['q' => 'KAUS', 'kategori' => $clothes->slug];

        $this->get(route('store.catalog', $filters + ['sort' => 'termurah']))
            ->assertOk()->assertSeeInOrder([$cheap->name, $expensive->name])
            ->assertSee('Rp35.000')->assertDontSee('KAUS-AKSESORI')->assertDontSee('JAKET');
        $this->get(route('store.catalog', $filters + ['sort' => 'termahal']))
            ->assertOk()->assertSeeInOrder([$expensive->name, $cheap->name]);
        $this->get(route('store.category', [$clothes->slug, 'q' => '%']))->assertOk()->assertSee('0 produk');
        $this->get('/katalog?q[]=x')->assertOk();
    }

    public function test_gallery_and_variants_use_product_data_and_primary_photo(): void
    {
        $this->withoutVite();
        $category = Category::create(['name' => 'Pakaian', 'slug' => 'pakaian']);
        $product = $this->product($category, 'kaus-varian', 85000);
        $product->variants()->create(['name' => 'XL', 'price' => 90000, 'stock' => 2]);
        $product->images()->create(['path' => 'products/side.webp', 'sort_order' => 0]);
        $product->images()->create(['path' => 'products/front.webp', 'sort_order' => 1, 'is_primary' => true]);

        $this->get(route('store.catalog'))->assertOk()->assertSee('products/front.webp')->assertDontSee('products/side.webp');
        $this->get(route('store.product', $product->slug))->assertOk()
            ->assertSeeInOrder(['products/front.webp', 'products/side.webp'])
            ->assertSee('Pilih varian')->assertSee('data-price="90000"', false);
    }

    private function product(Category $category, string $slug, int $price, ?int $variantPrice = null): Product
    {
        $product = $category->products()->create([
            'name' => strtoupper($slug),
            'slug' => $slug,
            'description' => 'Contoh merchandise sekolah.',
            'base_price' => $price,
        ]);

        $product->variants()->create(['name' => 'Default', 'price' => $variantPrice, 'stock' => 4]);

        return $product;
    }
}
