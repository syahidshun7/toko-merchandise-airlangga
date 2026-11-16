<?php

namespace Tests\Feature;

use App\Models\CartItem;
use App\Models\Category;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PreorderTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_enable_preorder_with_an_estimate_and_guest_can_checkout_without_stock(): void
    {
        $this->withoutVite();
        $category = Category::create(['name' => 'Pakaian', 'slug' => 'pakaian']);
        StoreSetting::forceCreate(['id' => 1, 'store_name' => 'Toko', 'school_name' => 'Sekolah', 'whatsapp_number' => '6281234567890']);
        $fields = [
            'name' => 'Jaket Sekolah',
            'category_id' => $category->id,
            'description' => 'Jaket untuk kegiatan sekolah.',
            'base_price' => 75000,
            'is_active' => 1,
            'is_featured' => 0,
            'is_preorder' => '1',
            'variants' => [['name' => 'M', 'stock' => 0], ['name' => 'L', 'stock' => 0]],
        ];

        $admin = User::factory()->create();
        $this->actingAs($admin)->post(route('admin.products.store'), $fields)
            ->assertSessionHasErrors('preorder_estimate');
        $this->post(route('admin.products.store'), $fields + ['preorder_estimate' => '7–10 hari kerja'])
            ->assertSessionHasNoErrors()->assertRedirect();
        $product = Product::sole();
        $this->assertTrue($product->is_preorder);

        auth()->logout();
        $this->get(route('store.catalog'))->assertOk()->assertSee('Pre-order (PO)');
        $this->get(route('store.preorder'))->assertOk()->assertSee('Jaket Sekolah')->assertSee('7–10 hari kerja');
        $this->get(route('store.product', $product->slug))->assertOk()->assertSee('7–10 hari kerja');
        $variant = $product->variants()->where('name', 'M')->sole();
        $this->post(route('store.cart.store'), ['variant_id' => $variant->id, 'quantity' => 2])
            ->assertSessionHasNoErrors()->assertRedirectToRoute('store.cart.index');
        $item = CartItem::sole();
        $this->patch(route('store.cart.update', $item), ['quantity' => 3])->assertSessionHasNoErrors();
        $this->get(route('store.cart.index'))->assertOk()->assertSee('7–10 hari kerja')->assertSee('Rp225.000');
        $response = $this->post(route('store.cart.whatsapp'))->assertStatus(303);
        $this->assertStringContainsString('Pre-order (PO) · Estimasi siap: 7–10 hari kerja', rawurldecode($response->headers->get('Location')));
        $this->assertSame(0, $variant->fresh()->stock);

        $variants = $product->variants()->get(['id', 'name', 'stock'])->toArray();
        $this->actingAs($admin)->patch(route('admin.products.update', $product), array_replace($fields, [
            'is_preorder' => '0',
            'variants' => $variants,
        ]))->assertSessionHasNoErrors();
        $this->assertNull($product->fresh()->preorder_estimate);
        $this->get(route('store.preorder'))->assertOk()->assertDontSee('Jaket Sekolah');
        auth()->logout();
        $this->post(route('store.cart.whatsapp'))->assertRedirectToRoute('store.cart.index')->assertSessionHasErrors('checkout');
    }
}
