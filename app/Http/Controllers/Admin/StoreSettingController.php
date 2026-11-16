<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSettingRequest;
use App\Models\StoreSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class StoreSettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings', ['setting' => StoreSetting::findOrFail(1)]);
    }

    public function update(StoreSettingRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['logo', 'remove_logo']);
        $logo = $request->file('logo');
        $path = null;

        if ($logo) {
            $path = $logo->store('store', 'public');

            if ($path === false) {
                throw new RuntimeException('Logo toko gagal disimpan.');
            }
        }

        try {
            $oldPath = DB::transaction(function () use ($request, $data, $path) {
                $setting = StoreSetting::query()->lockForUpdate()->findOrFail(1);
                $oldPath = $setting->logo_path;
                $setting->fill($data);

                if ($path || $request->boolean('remove_logo')) {
                    $setting->logo_path = $path;
                }

                $setting->save();

                return $oldPath !== $setting->logo_path ? $oldPath : null;
            });
        } catch (Throwable $error) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }

            throw $error;
        }

        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        return to_route('admin.settings.edit')->with('status', 'Pengaturan toko disimpan.');
    }
}
