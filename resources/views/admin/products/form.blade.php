@extends('admin.layout')

@php
    $title = $product->exists ? 'Edit produk' : 'Tambah produk';
    $variantRows = old('variants', $product->exists ? $product->variants->toArray() : [['name' => 'Default', 'stock' => 0]]);
    $variantRows = is_array($variantRows) ? array_values(array_filter($variantRows, 'is_array')) : [];
    $removedImages = old('remove_images', []);
    $removedImages = is_array($removedImages) ? $removedImages : [];
@endphp

@section('title', $title)

@section('content')
    <x-admin.page :title="$title">
        @if ($categories->isEmpty())
            <p class="admin-panel p-5">Buat kategori sebelum menambahkan produk. <a class="admin-link" href="{{ route('admin.categories.create') }}">Tambah kategori</a></p>
        @else
            <form class="max-w-4xl space-y-5" method="POST" enctype="multipart/form-data"
                action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
                @csrf
                @if ($product->exists)
                    @method('PATCH')
                @endif

                <section class="admin-panel space-y-4 p-5 sm:p-6" aria-labelledby="product-heading">
                    <h2 id="product-heading" class="text-lg font-semibold">Informasi produk</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <x-admin.field name="name" label="Nama produk" :value="$product->name" maxlength="150" required />
                        <div>
                            <label class="admin-label" for="category_id">Kategori</label>
                            <select id="category_id" name="category_id" class="admin-input" required>
                                <option value="">Pilih kategori</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}{{ $category->is_active ? '' : ' (nonaktif)' }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <x-admin.field name="description" label="Deskripsi" type="textarea" :value="$product->description" rows="4" maxlength="10000" required />
                    <div class="max-w-xs">
                        <x-admin.field name="base_price" label="Harga dasar (Rp)" type="number" :value="$product->base_price" min="0" max="999999999999" required />
                    </div>
                    <div class="flex flex-wrap gap-x-6">
                        <x-admin.toggle name="is_active" label="Produk aktif" :checked="$product->is_active" />
                        <x-admin.toggle name="is_featured" label="Produk unggulan" :checked="$product->is_featured" />
                        <x-admin.toggle name="is_preorder" label="Pre-order (PO)" :checked="$product->is_preorder" />
                    </div>
                    <x-admin.field name="preorder_estimate" label="Estimasi siap untuk PO" :value="$product->preorder_estimate" maxlength="120" hint="Wajib jika PO aktif. Contoh 7–10 hari kerja." />
                </section>

                <section class="admin-panel space-y-4 p-5 sm:p-6" aria-labelledby="variants-heading" data-variant-editor>
                    <h2 id="variants-heading" class="text-lg font-semibold">Varian dan stok</h2>
                    <p class="text-sm text-slate-600">Untuk pilihan terpisah di etalase, isi ukuran dan warna di setiap baris. Untuk varian biasa, isi nama saja. Harga varian kosong mengikuti harga dasar.</p>
                    <div class="space-y-3" data-variant-list>
                        @forelse ($variantRows as $index => $row)
                            @include('admin.products.variant-row', ['index' => $index, 'row' => $row])
                        @empty
                            @include('admin.products.variant-row', ['index' => 0, 'row' => ['name' => 'Default', 'stock' => 0]])
                        @endforelse
                    </div>
                    <template data-variant-template>
                        @include('admin.products.variant-row', ['index' => 0, 'row' => []])
                    </template>
                    <button class="admin-button admin-secondary" type="button" data-add-variant>Tambah varian</button>
                </section>

                <section class="admin-panel space-y-4 p-5 sm:p-6" aria-labelledby="photos-heading">
                    <h2 id="photos-heading" class="text-lg font-semibold">Foto produk</h2>
                    @if ($product->exists && $product->images->isNotEmpty())
                        <p class="text-sm text-slate-600">Angka terkecil ditampilkan lebih dulu. Foto baru ditambahkan di akhir.</p>
                        <div class="grid gap-3 sm:grid-cols-2">
                            @foreach ($product->images as $image)
                                <div class="flex items-start gap-3 rounded-lg border border-slate-200 p-3">
                                    <img class="size-20 shrink-0 rounded-md bg-slate-100 object-cover" src="{{ asset('storage/'.$image->path) }}" alt="Foto {{ $loop->iteration }}">
                                    <div class="min-w-0 space-y-2">
                                        <label class="block text-sm font-semibold">
                                            Urutan foto {{ $loop->iteration }}
                                            <input class="admin-input" type="number" name="image_order[{{ $image->id }}]" value="{{ is_scalar(old('image_order.'.$image->id, $image->sort_order)) ? old('image_order.'.$image->id, $image->sort_order) : $image->sort_order }}" min="0" max="10000" required>
                                        </label>
                                        <label class="flex min-h-11 items-center gap-2 text-sm">
                                            <input class="size-4 accent-red-700" type="checkbox" name="remove_images[]" value="{{ $image->id }}" @checked(in_array($image->id, $removedImages))>
                                            Hapus foto {{ $loop->iteration }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                    <div>
                        <label class="admin-label" for="photos">Unggah foto</label>
                        <input class="admin-input" id="photos" name="photos[]" type="file" accept="image/jpeg,image/png,image/webp" multiple>
                        <p class="mt-1 text-sm text-slate-600">Maksimal 10 foto per produk. JPG, PNG, atau WebP hingga 4 MB per foto.</p>
                    </div>
                    <div>
                        <label class="admin-label" for="primary_image">Foto utama</label>
                        <select class="admin-input" id="primary_image" name="primary_image">
                            <option value="">Otomatis · pertahankan foto utama jika masih ada</option>
                            @if ($product->exists)
                                @foreach ($product->images as $image)
                                    <option value="{{ $image->id }}" @selected(is_scalar(old('primary_image')) && (string) old('primary_image') === (string) $image->id)>Foto {{ $loop->iteration }}{{ $image->is_primary ? ' (utama saat ini)' : '' }}</option>
                                @endforeach
                            @endif
                            <option value="0" @selected(old('primary_image') === '0')>Foto pertama dari unggahan baru</option>
                        </select>
                    </div>
                </section>

                <div class="flex flex-wrap gap-2">
                    <button class="admin-button admin-primary" type="submit">Simpan produk</button>
                    <a class="admin-button admin-secondary" href="{{ route('admin.products.index') }}">Batal</a>
                </div>
            </form>
        @endif
    </x-admin.page>
@endsection

