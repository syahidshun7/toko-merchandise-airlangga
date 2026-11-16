@extends('store.layout')

@section('title', e($product->name) . ' · ' . e($setting->store_name))
@section('description', e(Str::limit(Str::squish($product->description), 155)))
@if ($product->images->isNotEmpty())
    @section('image', e(asset('storage/'.$product->images->first()->path)))
@endif

@section('content')
    <div class="store-wrap store-page">
        <nav class="store-breadcrumb" aria-label="Jejak navigasi">
            <a href="{{ route('store.catalog') }}">Katalog</a>
            <span aria-hidden="true">/</span>
            <a href="{{ route('store.category', $product->category->slug) }}">{{ $product->category->name }}</a>
            <span aria-hidden="true">/</span>
            <span aria-current="page" class="wrap-anywhere">{{ $product->name }}</span>
        </nav>
        <article class="store-detail" data-store-product>
            <div class="store-gallery">
                <div class="store-detail-photo">
                    @if ($product->images->count() > 1)
                        <div class="product-gallery-arrows" data-gallery-controls hidden>
                            <button type="button" data-gallery-step="-1" aria-label="Foto sebelumnya"><x-store.icon name="left" /></button>
                            <button type="button" data-gallery-step="1" aria-label="Foto berikutnya"><x-store.icon name="right" /></button>
                        </div>
                    @endif
                    @if ($product->images->isNotEmpty())
                        <img data-gallery-image src="{{ asset('storage/'.$product->images->first()->path) }}" alt="Foto {{ $product->name }} 1">
                    @else
                        <span>Foto belum tersedia</span>
                    @endif
                </div>
                @if ($product->images->contains(fn ($photo) => in_array($photo->path, ['products/demo-kaus-ai.webp', 'products/demo-tas-ai.webp'], true)))
                    <p class="text-xs text-slate-600 lg:col-start-2">Galeri memuat foto ilustrasi.</p>
                @endif
                @if ($product->images->count() > 1)
                    <div class="product-gallery-dots" data-gallery-dots hidden aria-hidden="true">
                        @foreach ($product->images as $photo)
                            <span data-gallery-dot @if ($loop->first) data-current @endif></span>
                        @endforeach
                    </div>
                    <div class="store-thumbnails" aria-label="Galeri produk">
                        @foreach ($product->images as $photo)
                            <button type="button" aria-label="Tampilkan foto {{ $loop->iteration }}" aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                                data-gallery-thumbnail data-src="{{ asset('storage/'.$photo->path) }}" data-alt="Foto {{ $product->name }} {{ $loop->iteration }}">
                                <img src="{{ asset('storage/'.$photo->path) }}" alt="" loading="lazy" decoding="async">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>
            <div class="store-detail-info">
                @if ($product->is_preorder)
                    <span class="store-po detail-status-badge">Pre-order (PO)</span>
                @elseif ($product->is_featured)
                    <span class="detail-status-badge">Unggulan</span>
                @endif
                <h1>{{ $product->name }}</h1>
                <p class="store-detail-price" data-product-price aria-live="polite">Rp{{ number_format($product->starting_price, 0, ',', '.') }}</p>
                @php($variant = $product->variants->count() === 1 ? $product->variants->first() : null)
                @php($hasDimensions = $product->variants->count() > 1 && $product->variants->every(fn ($option) => filled($option->size) && filled($option->color)))
                @php($canOrder = $product->variants->isNotEmpty() && ($product->is_preorder || $product->variants_sum_stock > 0))
                <div class="market-product-facts">
                    <p class="detail-fact">
                        <x-store.icon name="package" />
                        <span>
                            <strong>{{ $product->is_preorder ? 'Pre-order' : 'Stok' }}:</strong>
                            <span data-product-stock aria-live="polite" class="detail-stock {{ !$product->is_preorder && $product->variants_sum_stock > 0 ? 'text-green-800' : '' }}">
                                @if ($product->is_preorder && $canOrder)
                                    Estimasi siap {{ $product->preorder_estimate }}
                                @elseif (!$product->variants_sum_stock)
                                    Stok habis
                                @else
                                    Tersedia ({{ number_format($variant?->stock ?? $product->variants_sum_stock, 0, ',', '.') }} unit)
                                @endif
                            </span>
                        </span>
                    </p>
                    <p class="detail-fact">
                        <x-store.icon name="shirt" />
                        <span><strong>Kategori:</strong> <a href="{{ route('store.category', $product->category->slug) }}">{{ $product->category->name }}</a></span>
                    </p>
                </div>
            </div>
                <form @if ($product->is_preorder) data-preorder @endif method="POST" action="{{ route('store.cart.store') }}" class="store-options">
                    @csrf
                    @if ($errors->any())
                        <div role="alert" class="rounded-lg bg-red-50 p-3 text-sm text-red-900">
                            @foreach ($errors->all() as $error)
                                <p>{{ $error }}</p>
                            @endforeach
                        </div>
                    @endif
                    @if ($variant)
                        <input type="hidden" name="variant_id" value="{{ $variant->id }}">
                    @endif
                    @if ($product->variants->count() > 1)
                        <div>
                            <label for="variant" @if ($hasDimensions) data-dimension-fallback @endif>Varian</label>
                            <select id="variant" name="variant_id" data-product-variant required>
                                <option value="" data-stock="0" data-price="{{ $product->starting_price }}">Pilih varian</option>
                                @foreach ($product->variants as $option)
                                    <option value="{{ $option->id }}" data-stock="{{ $product->quantityLimit($option->stock) }}" @if ($hasDimensions) data-size="{{ $option->size }}" data-color="{{ $option->color }}" @endif data-price="{{ $option->price ?? $product->base_price }}" @disabled(!$product->is_preorder && $option->stock === 0) @selected(is_scalar(old('variant_id')) && (string) old('variant_id') === (string) $option->id)>
                                        {{ $option->name }}{{ !$product->is_preorder && $option->stock === 0 ? ' · Stok habis' : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @if ($hasDimensions)
                                <div class="mt-2 grid gap-3" data-variant-dimensions hidden>
                                    @foreach (['size' => 'Ukuran', 'color' => 'Warna'] as $attribute => $label)
                                        <div role="group" aria-label="{{ $label }}">
                                            <p class="mb-1.5 text-sm font-semibold">{{ $label }}</p>
                                            <div class="store-variant-buttons">
                                                @foreach ($product->variants->pluck($attribute)->unique() as $value)
                                                    <button type="button" data-dimension="{{ $attribute }}" data-value="{{ $value }}" aria-pressed="false">{{ $value }}</button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                    <p class="text-sm text-slate-600" data-dimension-status aria-live="polite"></p>
                                </div>
                            @else
                            <div class="store-variant-buttons" role="group" aria-label="Pilih varian" hidden>
                                @foreach ($product->variants as $option)
                                    <button type="button" data-variant-choice="{{ $option->id }}" aria-pressed="false" @disabled(!$product->is_preorder && $option->stock === 0)>
                                        {{ $option->name }}{{ !$product->is_preorder && $option->stock === 0 ? ' · Stok habis' : '' }}
                                    </button>
                                @endforeach
                            </div>
                            @endif
                        </div>
                    @elseif ($variant && $variant->name !== 'Default')
                        <p class="text-sm font-semibold">Varian {{ $variant->name }}</p>
                    @endif

                    <div>
                        <label for="quantity">Jumlah</label>
                        <div class="store-stepper">
                            <button type="button" data-quantity-step="-1" aria-label="Kurangi jumlah" hidden>−</button>
                            <input id="quantity" name="quantity" type="number" inputmode="numeric" min="1" max="{{ max(1, $variant ? $product->quantityLimit($variant->stock) : 2147483647) }}" value="{{ is_numeric(old('quantity')) ? old('quantity') : 1 }}" data-product-quantity required @disabled(!$canOrder)>
                            <button type="button" data-quantity-step="1" aria-label="Tambah jumlah" hidden>+</button>
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <button class="store-button store-button-gold" type="submit" data-product-add @disabled(!$canOrder)>
                            <x-store.cart-icon /> Masukkan ke Keranjang
                        </button>
                        <a class="store-button market-cart-link" href="{{ route('store.cart.index') }}" aria-label="Pesan via WhatsApp melalui keranjang">
                            <x-store.icon name="chat" /> Pesan via WhatsApp
                        </a>
                    </div>
                </form>
        </article>

        <section class="store-description" aria-labelledby="description-heading">
            <h2 id="description-heading">Deskripsi</h2>
            <p class="mt-4 whitespace-pre-line wrap-anywhere text-slate-700">{{ $product->description }}</p>
        </section>

        <x-store.collection title="Produk terkait" :products="$related" />
    </div>
@endsection


