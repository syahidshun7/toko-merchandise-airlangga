<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BannerRequest;
use App\Models\Banner;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class BannerController extends Controller
{
    public function index(): View
    {
        return view('admin.banners.index', ['banners' => Banner::orderBy('sort_order')->orderBy('id')->paginate(20)]);
    }

    public function create(): View
    {
        return $this->edit(new Banner(['is_active' => true, 'sort_order' => 0]));
    }

    public function edit(Banner $banner): View
    {
        return view('admin.banners.form', compact('banner'));
    }

    public function store(BannerRequest $request): RedirectResponse
    {
        return $this->save($request, new Banner);
    }

    public function update(BannerRequest $request, Banner $banner): RedirectResponse
    {
        return $this->save($request, $banner);
    }

    public function destroy(Banner $banner): RedirectResponse
    {
        $path = DB::transaction(function () use ($banner) {
            $banner = Banner::lockForUpdate()->findOrFail($banner->id);
            $banner->delete();

            return $banner->image_path;
        });
        Storage::disk('public')->delete($path);

        return to_route('admin.banners.index')->with('status', 'Banner dihapus.');
    }

    private function save(BannerRequest $request, Banner $banner): RedirectResponse
    {
        $image = $request->file('image');
        $path = $image?->store('banners', 'public');
        if ($path === false) {
            throw new RuntimeException('Gambar banner gagal disimpan.');
        }

        try {
            $oldPath = DB::transaction(function () use ($request, $banner, $path) {
                $banner = $banner->exists ? Banner::lockForUpdate()->findOrFail($banner->id) : $banner;
                $oldPath = $banner->image_path;
                $banner->fill($request->safe()->except('image'));
                if ($path !== null) {
                    $banner->image_path = $path;
                }
                $banner->save();

                return $path !== null ? $oldPath : null;
            });
        } catch (Throwable $error) {
            if ($path !== null) {
                Storage::disk('public')->delete($path);
            }
            throw $error;
        }

        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return to_route('admin.banners.index')->with('status', 'Banner disimpan.');
    }
}
