@extends('store.layout')

@section('content')
    <x-store.banner-carousel :banners="$banners">
        <div class="market-hero-art">
            <img src="{{ asset('images/merchandise-banner.webp') }}"
                srcset="{{ asset('images/merchandise-banner-small.webp') }} 720w, {{ asset('images/merchandise-banner.webp') }} 1440w"
                sizes="(max-width: 639px) 100vw, 60vw" width="1440" height="960"
                alt="Ilustrasi koleksi kaus, tas kanvas, buku catatan, dan tali lanyard" fetchpriority="high">
        </div>
        <div class="store-wrap market-hero-inner">
            <div class="market-hero-copy">
                <span class="hero-store-badge">Official Store</span>
                <h1 id="merchandise-title">Merchandise <span>SMK TI Airlangga</span></h1>
                <p>Jelajahi pakaian dan aksesori dari {{ $setting->school_name }}.</p>
                <a class="store-button market-yellow" href="#etalase">Belanja sekarang <span aria-hidden="true">→</span></a>
            </div>
        </div>
    </x-store.banner-carousel>

    <div class="store-wrap mobile-home-content">
        <nav class="market-shortcuts" aria-label="Tautan cepat">
            <a href="#kategori"><x-store.icon name="grid" /><div><strong>Kategori produk</strong><span>Jelajahi koleksi sekolah</span></div></a>
            <a href="{{ route('store.preorder') }}"><x-store.icon name="calendar" /><div><strong>Pre-order (PO)</strong><span>Cek estimasi pada produk PO</span></div></a>
            <a href="{{ route('store.cart.index') }}"><x-store.icon name="chat" /><div><strong>Pemesanan via WhatsApp</strong><span>Lanjutkan dari keranjang</span></div></a>
        </nav>

        <div id="etalase" class="market-shelf">
            <aside id="kategori" class="market-categories" aria-labelledby="category-heading">
                <h2 id="category-heading">Kategori</h2>
                <nav aria-label="Kategori produk">
                    <a href="{{ route('store.catalog') }}" class="market-category-current"><x-store.icon name="grid" />Semua produk <span aria-hidden="true">→</span></a>
                    @foreach ($categories as $category)
                        @php($categoryIcon = match (Str::slug($category->name)) {
                            'pakaian' => 'shirt',
                            'tas' => 'bag',
                            'tumbler', 'tumbler-botol' => 'bottle',
                            'alat-tulis' => 'book',
                            default => 'grid',
                        })
                        <a href="{{ route('store.category', $category->slug) }}"><x-store.icon :name="$categoryIcon" />{{ $category->name }}</a>
                    @endforeach
                </nav>
            </aside>

            <section class="min-w-0 mobile-home-products" aria-labelledby="shelf-heading">
                <div class="market-section-head">
                    <h2 id="shelf-heading">Etalase produk</h2>
                    <a class="store-text-link" href="{{ route('store.catalog') }}">Lihat semua <span aria-hidden="true">→</span></a>
                </div>
                @php($showcase = $featured->concat($latest)->unique('id'))
                @if ($showcase->isNotEmpty())
                    <div class="store-grid market-grid">
                        @foreach ($showcase as $product)
                            <x-store.card :product="$product" :show-featured="true" />
                        @endforeach
                    </div>
                @else
                    <p class="store-empty">Produk akan muncul saat katalog tersedia.</p>
                @endif
            </section>
        </div>

        <section id="pre-order" class="market-po">
            <x-store.icon name="calendar" class="po-banner-icon size-9" />
            <div>
                <h2>Program Pre-order (PO)</h2>
                <p>Produk berlabel PO menampilkan estimasi kesiapan di halaman detail.</p>
            </div>
            <a class="store-button store-button-blue" href="{{ route('store.preorder') }}">Lihat produk PO <span aria-hidden="true">→</span></a>
        </section>
    </div>
@endsection

