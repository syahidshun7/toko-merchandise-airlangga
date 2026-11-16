@props(['product', 'showFeatured' => false])

<article class="store-card">
    <a href="{{ route('store.product', $product->slug) }}" class="store-card-link">
        <div class="store-card-media">
            @if ($product->is_preorder || ($showFeatured && $product->is_featured))
                <span class="market-card-badge">{{ $product->is_preorder ? 'Pre-order (PO)' : 'Unggulan' }}</span>
            @endif
            @if ($product->coverImage)
                <img src="{{ asset('storage/'.$product->coverImage->path) }}" alt="" loading="lazy" decoding="async">
            @else
                <span>Foto belum tersedia</span>
            @endif
        </div>
        <div class="store-card-body">
            <h3>{{ $product->name }}</h3>
            @if (in_array($product->coverImage?->path, ['products/demo-kaus-ai.webp', 'products/demo-tas-ai.webp'], true))
                <p class="text-xs text-slate-600">Foto ilustrasi</p>
            @endif
            <p class="text-xs text-slate-600">{{ $product->category->name }}</p>
            <p class="store-price">Rp{{ number_format($product->starting_price, 0, ',', '.') }}</p>
            @unless ($product->is_preorder)
                <p class="text-sm {{ $product->variants_sum_stock > 0 ? 'text-emerald-800' : 'text-slate-600' }}">{{ $product->variants_sum_stock > 0 ? 'Stok '.number_format($product->variants_sum_stock, 0, ',', '.').' unit' : 'Stok habis' }}</p>
            @endunless
            <span class="store-card-action">Lihat detail <span aria-hidden="true"><x-store.cart-icon class="size-4" /></span></span>
        </div>
    </a>
</article>

