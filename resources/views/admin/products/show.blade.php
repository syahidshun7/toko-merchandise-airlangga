@extends('admin.layout')

@section('title', $product->name)

@section('content')
    <x-admin.page :title="$product->name" :description="$product->category->name">
        <x-slot:actions>
            <a class="admin-button admin-secondary" href="{{ route('admin.products.index') }}">Daftar produk</a>
            <a class="admin-button admin-primary" href="{{ route('admin.products.edit', $product) }}">Edit produk</a>
        </x-slot:actions>

        <div class="grid items-start gap-5 lg:grid-cols-2">
            <section class="admin-panel space-y-4 p-5 sm:p-6" aria-labelledby="detail-heading">
                <h2 id="detail-heading" class="text-lg font-semibold">Informasi produk</h2>
                <dl class="grid gap-4 sm:grid-cols-2">
                    <div><dt class="text-sm text-slate-600">Harga dasar</dt><dd class="font-semibold">Rp{{ number_format($product->base_price, 0, ',', '.') }}</dd></div>
                    <div><dt class="text-sm text-slate-600">Status</dt><dd class="font-semibold">{{ $product->is_active ? 'Aktif' : 'Nonaktif' }}{{ $product->is_featured ? ' · Unggulan' : '' }}</dd></div>
                    @if ($product->is_preorder)
                        <div><dt class="text-sm text-slate-600">Pre-order (PO)</dt><dd>{{ $product->preorder_estimate }}</dd></div>
                    @endif
                    <div class="sm:col-span-2"><dt class="text-sm text-slate-600">Slug</dt><dd class="wrap-break-word">{{ $product->slug }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-sm text-slate-600">Deskripsi</dt><dd class="wrap-break-word whitespace-pre-wrap">{{ $product->description }}</dd></div>
                </dl>
                @if ($product->images->isNotEmpty())
                    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3">
                        @foreach ($product->images as $image)
                            <figure>
                                <img class="aspect-square w-full rounded-lg bg-slate-100 object-cover" src="{{ asset('storage/'.$image->path) }}" alt="Foto {{ $product->name }} {{ $loop->iteration }}">
                                @if ($image->is_primary)
                                    <figcaption class="mt-1 text-sm text-slate-600">Foto utama</figcaption>
                                @endif
                            </figure>
                        @endforeach
                    </div>
                @else
                    <p class="text-sm text-slate-600">Belum ada foto.</p>
                @endif
            </section>

            <section class="admin-panel p-5 sm:p-6" aria-labelledby="variant-heading">
                <h2 id="variant-heading" class="text-lg font-semibold">Varian dan stok</h2>
                <ul class="mt-3 divide-y divide-slate-200">
                    @foreach ($product->variants as $variant)
                        <li class="flex flex-wrap justify-between gap-2 py-3 text-sm">
                            <span class="font-semibold">{{ $variant->name }}</span>
                            <span>Rp{{ number_format($variant->price ?? $product->base_price, 0, ',', '.') }}</span>
                            <span>{{ $variant->stock }} unit</span>
                        </li>
                    @endforeach
                </ul>
            </section>
        </div>
    </x-admin.page>
@endsection

