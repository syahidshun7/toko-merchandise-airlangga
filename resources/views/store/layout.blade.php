<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <link rel="icon" href="{{ asset('images/merchandise.ico') }}" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', e($setting->store_name))</title>
    <meta name="description" content="@yield('description', e('Jelajahi katalog merchandise '.$setting->school_name.'.'))">
    <meta property="og:title" content="@yield('title', e($setting->store_name))">
    <meta property="og:description" content="@yield('description', e('Jelajahi katalog merchandise '.$setting->school_name.'.'))">
    <meta property="og:image" content="@yield('image', asset('images/merchandise-banner.webp'))">
    <meta property="og:type" content="website">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="store min-h-screen text-slate-950 antialiased">
    <a href="#isi" class="sr-only focus:not-sr-only focus:absolute focus:z-20 focus:bg-white focus:p-3">Lewati ke konten</a>
    <header class="store-topbar">
        <div class="store-wrap store-header">
            <a class="store-brand" href="{{ route('store.home') }}">
                @if ($setting->logo_path)
                    <img src="{{ asset('storage/'.$setting->logo_path) }}" alt="" width="48" height="48">
                @else
                    <span class="store-brand-mark" aria-hidden="true">M</span>
                @endif
                <span><strong>{{ $setting->store_name }}</strong><small>{{ $setting->school_name }}</small></span>
            </a>
            <div class="mobile-header-actions">
                <a class="mobile-cart-icon" href="{{ route('store.cart.index') }}" aria-label="Keranjang, {{ $cartCount }} item">
                    <x-store.cart-icon /><span>{{ $cartCount }}</span>
                </a>
                <details class="mobile-menu">
                    <summary aria-label="Menu toko"><x-store.icon name="menu" /></summary>
                    <div>
                        <a href="{{ route('store.catalog') }}#q">Cari produk</a>
                        <a href="{{ route('store.home') }}#kategori">Kategori</a>
                    </div>
                </details>
            </div>
            <nav class="store-desktop-nav" aria-label="Navigasi toko">
                <a href="{{ route('store.home') }}" @if (request()->routeIs('store.home')) aria-current="page" @endif>Beranda</a>
                <a href="{{ route('store.catalog') }}" @if (request()->routeIs('store.catalog', 'store.category', 'store.product')) aria-current="page" @endif>Katalog</a>
                <a href="{{ route('store.home') }}#kategori">Kategori</a>
                <a href="{{ route('store.preorder') }}">PO</a>
                <a href="{{ route('store.home') }}#informasi-toko">Tentang</a>
                <a class="market-search" href="{{ route('store.catalog') }}#q" aria-label="Cari produk"><x-store.icon name="search" /></a>
                <a class="gap-1.5" href="{{ route('store.cart.index') }}" @if (request()->routeIs('store.cart.index')) aria-current="page" @endif>
                    <x-store.cart-icon class="size-4" />
                    Keranjang <span class="rounded-full bg-blue-50 px-2 py-0.5 text-xs tabular-nums text-school-blue">{{ $cartCount }}</span>
                </a>
            </nav>
        </div>
    </header>
    <main id="isi">
        @yield('content')
    </main>
    <footer class="store-footer" id="informasi-toko">
        <div class="store-wrap market-footer-inner">
            <div>
                <p class="font-semibold">{{ $setting->store_name }}</p>
                <p class="text-sm text-blue-100">{{ $setting->school_name }}</p>
                @if ($setting->description)
                    <p class="mt-2 whitespace-pre-line text-sm text-blue-100">{{ $setting->description }}</p>
                @endif
            </div>
            <nav class="grid content-start gap-2" aria-label="Menu footer">
                <p class="font-bold">Menu</p>
                <a href="{{ route('store.home') }}">Beranda</a>
                <a href="{{ route('store.catalog') }}">Katalog</a>
                <a href="{{ route('store.preorder') }}">Pre-order (PO)</a>
            </nav>
            @if ($setting->address || $setting->whatsapp_number)
                <div class="grid content-start gap-2">
                    <p class="font-bold">Kontak dan informasi</p>
                    @if ($setting->address)
                        <address class="whitespace-pre-line text-sm not-italic text-blue-100">{{ $setting->address }}</address>
                    @endif
                    @if ($setting->whatsapp_number)
                        <a href="https://wa.me/{{ $setting->whatsapp_number }}">+{{ $setting->whatsapp_number }}</a>
                    @endif
                </div>
            @endif
            <div class="grid content-start gap-2">
                <p class="font-bold">Pemesanan</p>
                <a href="{{ route('store.cart.index') }}">WhatsApp melalui keranjang</a>
            </div>
        </div>
    </footer>
    <nav class="mobile-bottom-nav" aria-label="Navigasi bawah">
        <a href="{{ route('store.home') }}" @if (request()->routeIs('store.home')) aria-current="page" @endif><x-store.icon name="home" />Beranda</a>
        <a href="{{ route('store.catalog') }}" @if (request()->routeIs('store.catalog', 'store.category', 'store.product')) aria-current="page" @endif><x-store.icon name="grid" />Produk</a>
        <a href="{{ route('store.preorder') }}" @if (request()->routeIs('store.preorder')) aria-current="page" @endif><x-store.icon name="calendar" />PO</a>
        <a href="{{ route('store.cart.index') }}" @if (request()->routeIs('store.cart.*')) aria-current="page" @endif><x-store.cart-icon />Keranjang</a>
    </nav>
</body>
</html>




