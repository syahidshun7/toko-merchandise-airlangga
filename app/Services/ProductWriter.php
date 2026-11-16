<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use Illuminate\Database\ForeignKeyConstraintViolationException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class ProductWriter
{
    public function save(Product $product, array $data): Product
    {
        $uploaded = [];

        try {
            foreach ($data['photos'] ?? [] as $photo) {
                $path = $photo->store('products', 'public');

                if ($path === false) {
                    throw new RuntimeException('Foto produk gagal disimpan.');
                }

                $uploaded[] = $path;
            }

            [$saved, $removed] = DB::transaction(function () use ($product, $data, $uploaded) {
                if ($product->exists) {
                    $product = Product::query()->lockForUpdate()->findOrFail($product->id);
                } else {
                    $product->slug = (Str::limit(Str::slug($data['name']), 200, '') ?: 'produk').'-'.Str::lower((string) Str::ulid());
                }

                $product->fill(Arr::only($data, ['category_id', 'name', 'description', 'base_price', 'is_active', 'is_featured']));
                if (array_key_exists('is_preorder', $data)) {
                    $product->is_preorder = (bool) $data['is_preorder'];
                    $product->preorder_estimate = $product->is_preorder ? $data['preorder_estimate'] : null;
                }

                $product->save();

                $this->syncVariants($product, $data['variants']);

                return [$product, $this->syncImages($product, $data, $uploaded)];
            });
        } catch (Throwable $error) {
            if ($uploaded !== []) {
                Storage::disk('public')->delete($uploaded);
            }

            throw $error;
        }

        if ($removed !== []) {
            Storage::disk('public')->delete($removed);
        }

        return $saved;
    }

    private function syncVariants(Product $product, array $rows): void
    {
        $rows = array_map(function (array $row) {
            if (isset($row['size'], $row['color'])) {
                $row['name'] = $row['size'].' · '.$row['color'];
            }

            return $row;
        }, $rows);

        if (collect($rows)->pluck('name')->map(fn ($name) => Str::lower($name))->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages(['variants' => 'Nama atau kombinasi ukuran dan warna harus unik.']);
        }

        $variants = $product->variants()->lockForUpdate()->get()->toBase()->keyBy('id');
        $keptIds = collect($rows)->pluck('id')->filter()->map(fn ($id) => (int) $id)->all();

        if (array_diff($keptIds, $variants->keys()->all()) !== []) {
            throw ValidationException::withMessages(['variants' => 'Ada varian yang tidak terdaftar pada produk ini.']);
        }

        $removed = $variants->except($keptIds);

        if ($product->variants()->whereKey($removed->keys()->all())->whereHas('cartItems')->exists()) {
            throw ValidationException::withMessages(['variants' => 'Varian yang masih ada di keranjang tidak dapat dihapus.']);
        }

        try {
            foreach ($removed as $variant) {
                $variant->delete();
            }
        } catch (ForeignKeyConstraintViolationException) {
            throw ValidationException::withMessages(['variants' => 'Varian yang masih ada di keranjang tidak dapat dihapus.']);
        }

        // Free changed names before applying the requested names so two variants can exchange names.
        foreach ($rows as $row) {
            $variant = $variants->get($row['id'] ?? 0);

            if ($variant && $variant->name !== $row['name']) {
                $variant->update(['name' => (string) Str::uuid()]);
            }
        }

        try {
            foreach ($rows as $row) {
                $variant = $variants->get($row['id'] ?? 0) ?? new ProductVariant;
                $variant->fill(Arr::only($row, ['name', 'size', 'color', 'price', 'stock']) + ['price' => null, 'size' => null, 'color' => null]);
                $product->variants()->save($variant);
            }
        } catch (UniqueConstraintViolationException) {
            throw ValidationException::withMessages(['variants' => 'Nama setiap varian harus unik dalam satu produk.']);
        }
    }

    private function syncImages(Product $product, array $data, array $uploaded): array
    {
        $images = $product->images()->orderBy('sort_order')->orderBy('id')->get()->toBase()->keyBy('id');
        $remove = array_map('intval', $data['remove_images'] ?? []);
        $order = $data['image_order'] ?? [];
        $primary = isset($data['primary_image']) ? (int) $data['primary_image'] : null;
        $referenced = array_merge($remove, array_keys($order), $primary > 0 ? [$primary] : []);

        if (array_diff($referenced, $images->keys()->all()) !== []) {
            throw ValidationException::withMessages(['photos' => 'Ada foto yang tidak terdaftar pada produk ini.']);
        }

        $remaining = $images->except($remove);
        if ($remaining->count() + count($uploaded) > 10) {
            throw ValidationException::withMessages(['photos' => 'Maksimal 10 foto per produk.']);
        }

        if (($primary === 0 && $uploaded === []) || ($primary > 0 && ! $remaining->has($primary))) {
            throw ValidationException::withMessages(['primary_image' => 'Pilih foto utama yang akan disimpan.']);
        }

        $removed = $images->only($remove);
        foreach ($removed as $image) {
            $image->delete();
        }

        $ordered = $remaining->sortBy(fn (ProductImage $image) => (int) ($order[$image->id] ?? $image->sort_order))->values();
        $main = $primary === null ? $ordered->firstWhere('is_primary', true)?->id : $primary;

        foreach ($uploaded as $path) {
            $image = $product->images()->create(['path' => $path, 'sort_order' => $ordered->count()]);
            $ordered->push($image);

            if ($main === 0) {
                $main = $image->id;
            }
        }

        $main ??= $ordered->first()?->id;

        foreach ($ordered as $position => $image) {
            $image->update(['sort_order' => $position, 'is_primary' => $image->id === $main]);
        }

        return $removed->pluck('path')->all();
    }
}
