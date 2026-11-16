<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class StoreDatabaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_creates_one_admin_and_a_catalog_with_variants(): void
    {
        $password = Str::random(24);

        config([
            'store.admin' => [
                'name' => 'Admin Pengujian',
                'email' => 'admin@example.test',
                'password' => $password,
            ],
            'store.seed_demo_catalog' => true,
        ]);

        $this->seed();
        $this->seed();

        $admin = User::query()->sole();
        $this->assertSame('admin@example.test', $admin->email);
        $this->assertTrue(Hash::check($password, $admin->password));
        $this->assertSame('SMK TI Airlangga Samarinda', StoreSetting::query()->sole()->school_name);

        $category = Category::query()->where('slug', 'pakaian')->sole();
        $shirt = $category->products()->sole();
        $this->assertTrue($shirt->category->is($category));
        $this->assertCount(3, $shirt->variants);
        $this->assertSame(85000, $shirt->variants->firstWhere('name', 'M')->effectivePrice());
        $this->assertSame(90000, $shirt->variants->firstWhere('name', 'XL')->effectivePrice());

        $bag = Product::query()->where('slug', 'tas-kanvas-airlangga-demo')->sole();
        $this->assertSame('Default', $bag->variants()->sole()->name);
    }

    public function test_product_soft_deletion_preserves_variant_and_cart_references(): void
    {
        [$product, $variant, $cart] = $this->createCartFixture();
        $image = $product->images()->create(['path' => 'products/sample.webp', 'is_primary' => true]);
        $item = $cart->items()->create(['product_variant_id' => $variant->id, 'quantity' => 2]);

        $this->assertInstanceOf(ProductImage::class, $image);
        $this->assertTrue($image->product->is($product));
        $this->assertTrue($item->cart->is($cart));
        $this->assertTrue($item->variant->is($variant));
        $this->assertTrue($variant->cartItems()->sole()->is($item));

        $product->delete();

        $this->assertSoftDeleted($product);
        $this->assertNull($variant->fresh()->product);
        $this->assertModelExists($image);
        $this->assertModelExists($item);
        $this->assertModelExists($variant);

        $cart->delete();

        $this->assertModelMissing($item);
        $this->assertModelExists($variant);
    }

    public function test_database_rejects_duplicate_variants_in_one_cart(): void
    {
        [, $variant, $cart] = $this->createCartFixture();
        $cart->items()->create(['product_variant_id' => $variant->id, 'quantity' => 1]);

        $this->expectException(QueryException::class);
        $cart->items()->create(['product_variant_id' => $variant->id, 'quantity' => 1]);
    }

    public function test_database_restricts_deleting_a_variant_used_in_a_cart(): void
    {
        [, $variant, $cart] = $this->createCartFixture();
        $cart->items()->create(['product_variant_id' => $variant->id, 'quantity' => 1]);

        $this->expectException(QueryException::class);
        $variant->delete();
    }

    public function test_database_restricts_deleting_a_category_with_a_soft_deleted_product(): void
    {
        [$product] = $this->createCartFixture();
        $product->delete();

        $this->expectException(QueryException::class);
        $product->category->delete();
    }

    /** @return array{Product, ProductVariant, Cart} */
    private function createCartFixture(): array
    {
        $category = Category::query()->create(['name' => 'Aksesori', 'slug' => 'aksesori']);
        $product = $category->products()->create([
            'name' => 'Tas Kanvas',
            'slug' => 'tas-kanvas',
            'description' => 'Tas merchandise.',
            'base_price' => 45000,
        ]);
        $variant = $product->variants()->create(['name' => 'Default', 'stock' => 3]);
        $cart = Cart::query()->create([
            'cart_token' => (string) Str::uuid(),
            'last_activity_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        return [$product, $variant, $cart];
    }
}
