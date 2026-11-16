<?php

namespace Tests\Feature;

use App\Models\Banner;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BannerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_banners_and_only_active_banners_appear(): void
    {
        $this->withoutVite();
        Storage::fake('public');

        $this->get(route('admin.banners.index'))->assertRedirectToRoute('admin.login');
        $this->actingAs(User::factory()->create());
        $this->get(route('admin.banners.create'))->assertOk();

        $data = ['title' => 'Promosi sekolah', 'sort_order' => 20, 'is_active' => 1];
        $this->post(route('admin.banners.store'), $data + ['image' => UploadedFile::fake()->image('banner.png')])
            ->assertSessionHasNoErrors()->assertRedirectToRoute('admin.banners.index');

        $banner = Banner::sole();
        $this->get(route('admin.banners.index'))->assertOk()->assertSee($banner->title);
        $this->get(route('admin.banners.edit', $banner))->assertOk();
        $this->get(route('store.home'))->assertOk()->assertSee($banner->title);

        $oldImage = $banner->image_path;
        Storage::disk('public')->assertExists($oldImage);
        $this->patch(route('admin.banners.update', $banner), array_replace($data, [
            'is_active' => 0,
            'image' => UploadedFile::fake()->image('replacement.png'),
        ]))->assertSessionHasNoErrors()->assertRedirectToRoute('admin.banners.index');

        $newImage = $banner->fresh()->image_path;
        Storage::disk('public')->assertMissing($oldImage);
        Storage::disk('public')->assertExists($newImage);
        $this->get(route('store.home'))->assertOk()->assertDontSee($banner->title);
        $this->delete(route('admin.banners.destroy', $banner))->assertRedirectToRoute('admin.banners.index');
        $this->assertModelMissing($banner);
        Storage::disk('public')->assertMissing($newImage);
    }
}
