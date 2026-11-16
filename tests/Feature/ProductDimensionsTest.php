<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDimensionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_size_and_color_combinations_keep_their_own_price_and_stock(): void
    {
        $this->withoutVite();
        $category = Category::create(['name' => 'Pakaian', 'slug' => 'pakaian']);
        $fields = [
            'name' => 'Kaus Sekolah',
            'description' => 'Kaus sekolah.',
            'category_id' => $category->id,
            'base_price' => 85000,
            'is_active' => 1,
            'is_featured' => 0,
        ];

        $this->actingAs(User::factory()->create());
        $this->post(route('admin.products.store'), $fields + [
            'variants' => [['size' => 'M', 'stock' => 3]],
        ])->assertSessionHasErrors('variants.0.color');

        $this->post(route('admin.products.store'), $fields + [
            'variants' => [
                ['size' => 'M', 'color' => 'Navy', 'stock' => 3],
                ['size' => 'M', 'color' => 'navy', 'stock' => 1],
            ],
        ])->assertSessionHasErrors('variants');

        $this->post(route('admin.products.store'), $fields + [
            'variants' => [
                ['size' => 'M', 'color' => 'Navy', 'stock' => 3, 'price' => 90000],
                ['size' => 'M', 'color' => 'Hitam', 'stock' => 0],
            ],
        ])->assertSessionHasNoErrors()->assertRedirect();

        $product = Product::sole();
        $navy = $product->variants()->where('color', 'Navy')->sole();
        $black = $product->variants()->where('color', 'Hitam')->sole();
        $this->assertSame('M · Navy', $navy->name);
        $this->get(route('store.product', $product->slug))->assertOk()
            ->assertSee('data-dimension="size"', false)
            ->assertSee('data-dimension="color"', false);

        $this->post(route('store.cart.store'), ['variant_id' => $black->id, 'quantity' => 1])->assertSessionHasErrors('quantity');
        $this->post(route('store.cart.store'), ['variant_id' => $navy->id, 'quantity' => 2])->assertSessionHasNoErrors();
        $this->assertSame($navy->id, CartItem::sole()->product_variant_id);
        $this->get(route('store.cart.index'))->assertOk()->assertSee('M · Navy')->assertSee('Rp180.000');
    }
}
