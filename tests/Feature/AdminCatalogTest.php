<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\Category;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    public function test_category_lifecycle_and_restriction_on_referenced_categories(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get(route('admin.categories.create'))->assertOk();
        $this->post(route('admin.categories.store'), ['name' => 'Pakaian', 'is_active' => 1])->assertRedirectToRoute('admin.categories.index');

        $category = Category::sole();
        $this->assertStringStartsWith('pakaian-', $category->slug);
        $this->get(route('admin.categories.edit', $category))->assertOk();
        $this->patch(route('admin.categories.update', $category), ['name' => 'Busana', 'is_active' => 0])
            ->assertRedirectToRoute('admin.categories.index');
        $this->assertFalse($category->fresh()->is_active);

        $product = Product::create($this->productFields($category) + ['slug' => 'kaus-contoh']);
        $product->delete();

        $this->from(route('admin.categories.index'))->delete(route('admin.categories.destroy', $category))
            ->assertRedirectToRoute('admin.categories.index')->assertSessionHasErrors('category');
        $this->assertModelExists($category);

        $empty = Category::create(['name' => 'Kosong', 'slug' => 'kosong']);
        $this->delete(route('admin.categories.destroy', $empty))->assertRedirectToRoute('admin.categories.index');
        $this->assertModelMissing($empty);
        $this->get(route('admin.categories.index'))->assertOk()->assertSee('Busana');
    }

    public function test_admin_can_save_reorder_and_remove_product_photos_and_edit_stock(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $category = Category::create(['name' => 'Pakaian', 'slug' => 'pakaian']);

        $this->get(route('admin.products.create'))->assertOk()->assertSee('Tambah varian');
        $this->post(route('admin.products.store'), $this->productFields($category) + [
            'variants' => [
                ['name' => 'M', 'stock' => 5],
                ['name' => 'L', 'stock' => 2, 'price' => 90000],
            ],
            'photos' => [UploadedFile::fake()->image('first.jpg'), UploadedFile::fake()->image('second.png')],
            'primary_image' => 0,
        ])->assertSessionHasNoErrors()->assertRedirect();

        $product = Product::sole();
        $this->assertStringStartsWith('kaus-sekolah-', $product->slug);
        [$first, $second] = $product->images()->orderBy('sort_order')->get()->all();
        [$medium, $large] = $product->variants()->orderBy('id')->get()->all();
        Storage::disk('public')->assertExists([$first->path, $second->path]);
        $this->assertTrue($first->is_primary);
        $this->get(route('admin.products.show', $product))->assertOk()->assertSee('Foto utama');
        $this->get(route('admin.products.edit', $product))->assertOk()->assertSee('Urutan foto');

        $updated = array_replace($this->productFields($category), ['name' => 'Kaus Alumni', 'is_active' => 0]);
        $variants = [
            ['id' => $medium->id, 'name' => 'L', 'stock' => 3],
            ['id' => $large->id, 'name' => 'M', 'stock' => 4, 'price' => 92000],
        ];
        $this->patch(route('admin.products.update', $product), $updated + [
            'variants' => $variants,
            'image_order' => [$first->id => 2, $second->id => 1],
            'primary_image' => $second->id,
        ])->assertSessionHasNoErrors()->assertRedirectToRoute('admin.products.show', $product);

        $this->assertSame(0, $second->fresh()->sort_order);
        $this->assertTrue($second->fresh()->is_primary);
        $this->assertFalse($first->fresh()->is_primary);
        $this->assertFalse($product->fresh()->is_active);
        $this->assertSame('L', $medium->fresh()->name);
        $this->assertSame(3, $medium->fresh()->stock);
        $this->assertSame(92000, $large->fresh()->price);

        $this->patch(route('admin.products.update', $product), $updated + [
            'variants' => $variants,
            'remove_images' => [$second->id],
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertTrue($first->fresh()->is_primary);
        Storage::disk('public')->assertMissing($second->path);

        $this->get(route('admin.products.index'))->assertOk()->assertSee('Kaus Alumni');
        $this->delete(route('admin.products.destroy', $product))->assertRedirectToRoute('admin.products.index');
        $this->assertSoftDeleted($product);
        $this->get(route('admin.products.show', $product))->assertNotFound();
        Storage::disk('public')->assertExists($first->path);
    }

    public function test_invalid_product_input_and_cart_referenced_variant_preserve_existing_data(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $category = Category::create(['name' => 'Pakaian', 'slug' => 'pakaian']);
        $fields = $this->productFields($category);
        $this->post(route('admin.products.store'), array_replace($fields, ['base_price' => -1]) + [
            'variants' => [['name' => 'Default', 'stock' => -1]],
            'photos' => [UploadedFile::fake()->create('invalid.pdf', 25)],
        ])->assertSessionHasErrors(['base_price', 'variants.0.stock', 'photos.0']);

        $product = Product::create($fields + ['slug' => 'kaus-contoh']);
        $variant = $product->variants()->create(['name' => 'Default', 'stock' => 1]);
        $cart = Cart::create(['cart_token' => Str::uuid()->toString(), 'last_activity_at' => now(), 'expires_at' => now()->addDay()]);
        $cart->items()->create(['product_variant_id' => $variant->id, 'quantity' => 1]);

        $this->patch(route('admin.products.update', $product), array_replace($fields, ['name' => 'Harus batal']) + [
            'variants' => [['name' => 'Baru', 'stock' => 3]],
            'photos' => [UploadedFile::fake()->image('rollback.jpg')],
        ])->assertSessionHasErrors('variants');

        $this->assertSame('Kaus Sekolah', $product->fresh()->name);
        $this->assertModelExists($variant);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_dashboard_counts_products_using_combined_variant_stock(): void
    {
        $this->actingAs(User::factory()->create());
        $category = Category::create(['name' => 'Pakaian', 'slug' => 'pakaian']);

        foreach ([0, 5, 6] as $stock) {
            $product = Product::create(array_replace($this->productFields($category), [
                'slug' => 'produk-'.$stock,
                'is_active' => $stock > 0,
            ]));
            $product->variants()->create(['name' => 'Default', 'stock' => $stock]);
        }

        $this->get(route('admin.dashboard'))->assertOk()->assertViewHas('stats', [
            'Total produk' => 3,
            'Total kategori' => 1,
            'Produk aktif' => 2,
            'Produk nonaktif' => 1,
            'Stok habis' => 1,
            'Stok menipis' => 1,
        ])->assertSee('Produk terbaru');
    }

    public function test_settings_normalize_whatsapp_and_replace_or_remove_logo(): void
    {
        Storage::fake('public');
        $this->actingAs(User::factory()->create());
        $setting = StoreSetting::create(['store_name' => 'Toko', 'school_name' => 'Sekolah']);
        $fields = ['store_name' => 'Koleksi Sekolah', 'school_name' => 'SMK TI Airlangga', 'whatsapp_number' => '0812-3456 7890'];

        $this->get(route('admin.settings.edit'))->assertOk();
        $this->patch(route('admin.settings.update'), $fields + ['logo' => UploadedFile::fake()->image('logo.png')])
            ->assertSessionHasNoErrors()->assertRedirectToRoute('admin.settings.edit');
        $setting->refresh();
        $oldPath = $setting->logo_path;
        Storage::disk('public')->assertExists($oldPath);
        $this->assertSame('6281234567890', $setting->whatsapp_number);
        $this->get(route('admin.settings.edit'))->assertOk()->assertSee('Logo toko saat ini');

        $this->patch(route('admin.settings.update'), $fields + ['logo' => UploadedFile::fake()->image('new.png')])
            ->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($oldPath);
        $newPath = $setting->fresh()->logo_path;
        Storage::disk('public')->assertExists($newPath);

        $this->patch(route('admin.settings.update'), $fields + ['remove_logo' => 1])->assertSessionHasNoErrors();
        Storage::disk('public')->assertMissing($newPath);
        $this->assertNull($setting->fresh()->logo_path);
    }

    public function test_profile_requires_current_password_to_change_password(): void
    {
        $original = Str::random(24);
        $replacement = Str::random(24);
        $admin = User::factory()->create(['password' => $original]);
        $this->actingAs($admin);

        $this->get(route('admin.profile.edit'))->assertOk();
        $fields = [
            'name' => 'Admin Baru',
            'email' => 'BARU@EXAMPLE.TEST',
            'password' => $replacement,
            'password_confirmation' => $replacement,
        ];

        $this->patch(route('admin.profile.update'), $fields)->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check($original, $admin->fresh()->password));

        $this->patch(route('admin.profile.update'), $fields + ['current_password' => $original])
            ->assertSessionHasNoErrors()->assertRedirectToRoute('admin.profile.edit');
        $this->assertSame('Admin Baru', $admin->fresh()->name);
        $this->assertSame('baru@example.test', $admin->fresh()->email);
        $this->assertTrue(Hash::check($replacement, $admin->fresh()->password));
    }

    public function test_admin_catalog_and_settings_require_authentication(): void
    {
        foreach (['products.index', 'products.create', 'categories.index', 'settings.edit', 'profile.edit'] as $name) {
            $this->get(route('admin.'.$name))->assertRedirectToRoute('admin.login');
        }

        $this->post(route('admin.products.store'))->assertRedirectToRoute('admin.login');
        $this->patch(route('admin.settings.update'))->assertRedirectToRoute('admin.login');
    }

    private function productFields(Category $category): array
    {
        return [
            'category_id' => $category->id,
            'name' => 'Kaus Sekolah',
            'description' => 'Kaus sekolah untuk kegiatan.',
            'base_price' => 85000,
            'is_active' => 1,
            'is_featured' => 0,
        ];
    }
}
