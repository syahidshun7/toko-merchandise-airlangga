<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreCartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_guest_cart_merges_matching_variants_and_totals_current_prices(): void
    {
        $medium = $this->variant();
        $large = $medium->product->variants()->create(['name' => 'L', 'price' => 90000, 'stock' => 5]);

        foreach ([[$medium, 2], [$medium, 1], [$large, 1]] as [$variant, $quantity]) {
            $this->post(route('store.cart.store'), ['variant_id' => $variant->id, 'quantity' => $quantity])
                ->assertSessionHasNoErrors()->assertRedirectToRoute('store.cart.index');
        }

        $cart = Cart::sole();
        $this->assertSessionHasToken($cart);
        $this->assertSame(2, $cart->items()->count());
        $item = $cart->items()->firstWhere('product_variant_id', $medium->id);
        $this->assertSame(3, $item->quantity);
        $this->get(route('store.cart.index'))->assertOk()->assertSee('Rp345.000');

        $medium->product->update(['base_price' => 80000]);
        $this->patch(route('store.cart.update', $item), ['quantity' => 2])->assertRedirectToRoute('store.cart.index');
        $this->get(route('store.cart.index'))->assertOk()->assertSee('Rp250.000');
        $this->assertSame(5, $medium->fresh()->stock);
        $this->delete(route('store.cart.destroy', $item))->assertRedirectToRoute('store.cart.index');
        $this->assertModelMissing($item);
        $this->delete(route('store.cart.clear'))->assertRedirectToRoute('store.cart.index');
        $this->get(route('store.cart.index'))->assertOk()->assertSee('Keranjang Anda masih kosong.');
    }

    public function test_stock_and_inactive_products_are_checked_without_altering_cart_quantity(): void
    {
        $variant = $this->variant();
        $data = ['variant_id' => $variant->id, 'quantity' => 4];
        $this->post(route('store.cart.store'), $data)->assertSessionHasNoErrors();
        $item = CartItem::sole();

        $this->post(route('store.cart.store'), $data)->assertSessionHasErrors('quantity');
        $this->patch(route('store.cart.update', $item), ['quantity' => 6])->assertSessionHasErrors('quantity');
        $this->assertSame(4, $item->fresh()->quantity);

        $variant->update(['stock' => 2]);
        $this->get(route('store.cart.index'))->assertOk()->assertSee('Stok tersisa 2 unit. Kurangi jumlah.');
        $this->patch(route('store.cart.update', $item), ['quantity' => 2])->assertRedirectToRoute('store.cart.index');
        $variant->product->category->update(['is_active' => false]);
        $this->post(route('store.cart.store'), $data)->assertSessionHasErrors('variant_id');
        $this->get(route('store.cart.index'))->assertOk()->assertSee('Item ini sudah tidak tersedia.');
        $this->assertSame(2, $item->fresh()->quantity);
        $this->assertSame(2, $variant->fresh()->stock);
    }

    public function test_other_sessions_cannot_view_modify_or_remove_cart_items(): void
    {
        $variant = $this->variant();
        $owner = Cart::create(['cart_token' => (string) Str::uuid(), 'last_activity_at' => now(), 'expires_at' => now()->addHour()]);
        $item = $owner->items()->create(['product_variant_id' => $variant->id, 'quantity' => 2]);
        $this->withSession(['cart_token' => (string) Str::uuid()]);

        $this->get(route('store.cart.index'))->assertOk()->assertDontSee('Kaus Airlangga');
        $this->patch(route('store.cart.update', $item), ['quantity' => 1])->assertNotFound();
        $this->delete(route('store.cart.destroy', $item))->assertNotFound();
        $this->delete(route('store.cart.clear'))->assertRedirectToRoute('store.cart.index');
        $this->assertSame(2, $item->fresh()->quantity);

        $this->withSession(['cart_token' => $owner->cart_token])
            ->get(route('store.cart.index'))->assertOk()->assertSee('Kaus Airlangga');
    }

    public function test_expired_cart_is_replaced_and_the_prune_command_cascades(): void
    {
        config(['store.cart_lifetime_hours' => 12]);
        $variant = $this->variant();
        $expired = Cart::create(['cart_token' => (string) Str::uuid(), 'last_activity_at' => now()->subDay(), 'expires_at' => now()]);
        $expiredItem = $expired->items()->create(['product_variant_id' => $variant->id, 'quantity' => 5]);
        $this->withSession(['cart_token' => $expired->cart_token]);

        $this->get(route('store.cart.index'))->assertOk()->assertSee('Keranjang Anda masih kosong.');
        $this->post(route('store.cart.store'), ['variant_id' => $variant->id, 'quantity' => 1])->assertSessionHasNoErrors();
        $active = Cart::whereKeyNot($expired->id)->sole();
        $this->assertSessionHasToken($active);
        $this->assertSame(12, (int) $active->last_activity_at->diffInHours($active->expires_at));

        $this->artisan('cart:prune')->assertSuccessful();
        $this->assertModelMissing($expired);
        $this->assertModelMissing($expiredItem);
        $this->assertModelExists($active);
    }

    private function assertSessionHasToken(Cart $cart): void
    {
        $this->assertTrue(Str::isUuid($cart->cart_token));
        $this->assertSame($cart->cart_token, session('cart_token'));
    }

    private function variant(): ProductVariant
    {
        $category = Category::create(['name' => 'Pakaian', 'slug' => 'pakaian']);
        $product = $category->products()->create([
            'name' => 'Kaus Airlangga',
            'slug' => 'kaus-airlangga',
            'description' => 'Kaus sekolah.',
            'base_price' => 85000,
        ]);

        return $product->variants()->create(['name' => 'M', 'stock' => 5]);
    }
}
