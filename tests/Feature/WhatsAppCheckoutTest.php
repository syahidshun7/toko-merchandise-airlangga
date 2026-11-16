<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\ProductVariant;
use App\Models\StoreSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WhatsAppCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_uses_current_prices_and_encodes_every_item_without_mutation(): void
    {
        $setting = StoreSetting::forceCreate(['id' => 1,
            'store_name' => 'Merchandise',
            'school_name' => 'Airlangga',
            'whatsapp_number' => '6281234567890',
        ]);
        $shirt = $this->variant('Kaus & Logo', 'XL', 85000);
        $bag = $this->variant('Tas Kanvas Édisi', 'Default', 45000);
        $cart = $this->cartWith($shirt, 2);
        $cart->items()->create(['product_variant_id' => $bag->id, 'quantity' => 1]);

        $shirt->update(['price' => 90000]);
        $bag->product->update(['base_price' => 50000]);
        $setting->update(['whatsapp_number' => '6281234567899']);
        $itemsBefore = $cart->items()->orderBy('id')->get()->toArray();
        $cartBefore = $cart->fresh()->getAttributes();

        $response = $this->withSession(['cart_token' => $cart->cart_token])
            ->post(route('store.cart.whatsapp'), ['total' => 1, 'whatsapp_number' => '11111111'])
            ->assertStatus(303);

        $url = $response->headers->get('Location');
        $this->assertStringStartsWith('https://wa.me/6281234567899?text=', $url);
        $this->assertStringContainsString('Kaus%20%26%20Logo', $url);
        parse_str(parse_url($url, PHP_URL_QUERY), $query);
        $this->assertStringContainsString("1. Kaus & Logo\n   Varian: XL\n   Jumlah: 2\n   Harga: Rp90.000\n   Subtotal: Rp180.000", $query['text']);
        $this->assertStringContainsString("2. Tas Kanvas Édisi\n   Varian: Default\n   Jumlah: 1\n   Harga: Rp50.000\n   Subtotal: Rp50.000", $query['text']);
        $this->assertStringContainsString('TOTAL: Rp230.000', $query['text']);
        $this->assertSame($cartBefore, $cart->fresh()->getAttributes());
        $this->assertSame($itemsBefore, $cart->items()->orderBy('id')->get()->toArray());
        $this->assertSame(5, $shirt->fresh()->stock);
        $this->assertSame(5, $bag->fresh()->stock);

        $this->withoutVite()->get(route('store.cart.index'))->assertOk()->assertSee('Rp230.000');
    }

    public function test_checkout_rechecks_stock_category_and_archived_products(): void
    {
        StoreSetting::forceCreate(['id' => 1, 'store_name' => 'Toko', 'school_name' => 'Airlangga', 'whatsapp_number' => '6281234567890']);
        $variant = $this->variant('Kaus', 'M', 85000);
        $cart = $this->cartWith($variant, 2);
        $this->withSession(['cart_token' => $cart->cart_token]);

        $variant->update(['stock' => 1]);
        $this->assertCheckoutBlocked();

        $variant->update(['stock' => 5]);
        $variant->product->category->update(['is_active' => false]);
        $this->assertCheckoutBlocked();

        $variant->product->category->update(['is_active' => true]);
        $variant->product->update(['is_active' => false]);
        $this->assertCheckoutBlocked();

        $variant->product->update(['is_active' => true]);
        $variant->product->delete();
        $this->assertCheckoutBlocked();

        $this->assertSame(2, $cart->items()->sole()->quantity);
    }

    public function test_checkout_rejects_unconfigured_number_and_expired_or_foreign_cart(): void
    {
        $variant = $this->variant('Kaus', 'M', 85000);
        $cart = $this->cartWith($variant, 1);

        $this->post(route('store.cart.whatsapp'))->assertRedirectToRoute('store.cart.index')
            ->assertSessionHasErrors(['checkout' => 'Keranjang kosong atau sudah kedaluwarsa.']);

        $this->withSession(['cart_token' => $cart->cart_token])
            ->post(route('store.cart.whatsapp'))->assertRedirectToRoute('store.cart.index')
            ->assertSessionHasErrors(['checkout' => 'Nomor WhatsApp toko belum dikonfigurasi.']);

        $cart->update(['expires_at' => now()->subMinute()]);
        $this->post(route('store.cart.whatsapp'))->assertRedirectToRoute('store.cart.index')
            ->assertSessionHasErrors(['checkout' => 'Keranjang kosong atau sudah kedaluwarsa.']);
    }

    private function assertCheckoutBlocked(): void
    {
        $this->post(route('store.cart.whatsapp'))->assertRedirectToRoute('store.cart.index')
            ->assertSessionHasErrors(['checkout' => 'Stok atau ketersediaan produk berubah. Periksa isi keranjang sebelum memesan.']);
    }

    private function cartWith(ProductVariant $variant, int $quantity): Cart
    {
        $cart = Cart::create(['cart_token' => (string) Str::uuid(), 'last_activity_at' => now(), 'expires_at' => now()->addHour()]);
        $cart->items()->create(['product_variant_id' => $variant->id, 'quantity' => $quantity]);

        return $cart;
    }

    private function variant(string $name, string $size, int $price): ProductVariant
    {
        $category = Category::firstOrCreate(['slug' => 'merchandise'], ['name' => 'Merchandise']);
        $product = $category->products()->create([
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => 'Merchandise sekolah.',
            'base_price' => $price,
        ]);

        return $product->variants()->create(['name' => $size, 'stock' => 5]);
    }
}
