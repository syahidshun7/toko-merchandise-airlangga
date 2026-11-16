@extends('store.layout')

@section('title', ($category ? e($category->name) : 'Katalog') . ' · ' . e($setting->store_name))

@section('content')
    <div class="store-wrap store-page">
        <h1 class="mobile-section-title">{{ $category?->name ?? 'Produk' }}</h1>
        <form class="store-catalog" method="GET" action="{{ route('store.catalog') }}">
            <aside class="store-filter-panel" aria-label="Filter produk">
                <label for="q">Cari produk</label>
                <div class="mobile-search-row">
                    <div class="relative min-w-0 flex-1">
                        <x-store.icon name="search" class="catalog-search-icon" />
                        <input id="q" type="search" name="q" value="{{ $search }}" maxlength="100" placeholder="Cari produk…">
                    </div>
                    <button type="submit" class="catalog-search-button" aria-label="Terapkan pencarian dan kategori"><x-store.icon name="filter" /></button>
                </div>

                <fieldset class="mt-5">
                    <legend class="font-bold text-school-blue">Kategori</legend>
                    <div class="store-category-options">
                        @foreach (collect([null])->concat($categories) as $option)
                            <label>
                                <input type="radio" name="kategori" value="{{ $option?->slug }}" @checked($category?->id === $option?->id)>
                                <span>{{ $option?->name ?? 'Semua kategori' }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                <button class="store-button store-button-gold mt-4 w-full" type="submit">Terapkan</button>
                @if ($search !== '' || $category || in_array($sort, ['termurah', 'termahal'], true))
                    <a class="store-text-link mt-1" href="{{ route('store.catalog') }}">Reset filter</a>
                @endif
            </aside>

            <section class="min-w-0" aria-label="Hasil katalog">
                <div class="store-results-head">
                    <h2>{{ $products->total() }} produk</h2>
                    <div>
                        <label for="sort">Urutkan</label>
                        <select id="sort" name="sort" data-store-sort>
                            <option value="terbaru">Terbaru</option>
                            <option value="termurah" @selected($sort === 'termurah')>Harga terendah</option>
                            <option value="termahal" @selected($sort === 'termahal')>Harga tertinggi</option>
                        </select>
                    </div>
                </div>

                @if ($products->isEmpty())
                    <p class="store-empty">Tidak ada produk yang cocok. Coba kata kunci atau kategori lain.</p>
                @else
                    <div class="store-grid">
                        @foreach ($products as $product)
                            <x-store.card :product="$product" />
                        @endforeach
                    </div>
                    <div class="mt-8">{{ $products->links() }}</div>
                @endif
            </section>
        </form>
    </div>
@endsection

