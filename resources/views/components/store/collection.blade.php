@props(['title', 'products', 'link' => false])

@if ($products->isNotEmpty())
    <section class="store-section">
        <div class="store-section-head">
            <h2>{{ $title }}</h2>
            @if ($link)
                <a class="store-text-link" href="{{ route('store.catalog') }}">Lihat katalog <span aria-hidden="true">→</span></a>
            @endif
        </div>
        <div class="store-grid">
            @foreach ($products as $product)
                <x-store.card :product="$product" />
            @endforeach
        </div>
    </section>
@endif

