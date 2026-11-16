<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-100 font-sans text-slate-950 antialiased selection:bg-amber-200">
    <header class="border-b border-white/15 bg-school-blue text-white">
        <div class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-3 px-5 py-4 sm:px-8">
            <div class="flex min-w-0 items-center gap-3">
                <span aria-hidden="true" class="h-8 w-1 shrink-0 rounded-full bg-school-gold"></span>
                <div class="min-w-0">
                    <p class="font-semibold leading-snug">{{ config('app.name') }}</p>
                    <p class="mt-0.5 text-sm text-blue-100">SMK TI Airlangga Samarinda</p>
                </div>
            </div>
            @auth
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="admin-button border border-white/40 text-white hover:bg-white/10 focus-visible:outline-school-gold">Keluar</button>
                </form>
            @endauth
        </div>
    </header>

    @auth
        <nav aria-label="Navigasi admin" class="border-b border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-5 px-5 sm:px-8">
                @foreach (['dashboard' => ['Dashboard', 'dashboard'], 'products' => ['Produk', 'products.index'], 'categories' => ['Kategori', 'categories.index'], 'banners' => ['Banner', 'banners.index'], 'settings' => ['Pengaturan toko', 'settings.edit'], 'profile' => ['Profil', 'profile.edit']] as $prefix => [$label, $route])
                    <a href="{{ route('admin.'.$route) }}"
                        @if (request()->routeIs('admin.'.$prefix, 'admin.'.$prefix.'.*')) aria-current="page" @endif
                        class="flex min-h-11 items-center border-b-2 border-transparent py-2 text-sm font-semibold text-slate-600 hover:text-school-blue aria-[current=page]:border-school-blue aria-[current=page]:text-school-blue">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </nav>
    @endauth

    @yield('content')
</body>
</html>

