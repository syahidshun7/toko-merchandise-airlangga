@props(['banners'])

<section class="store-carousel" aria-label="Banner toko" aria-roledescription="carousel" data-banner-carousel>
    <div class="market-hero" data-banner-slide>
        {{ $slot }}
    </div>
    @foreach ($banners as $banner)
        <div class="market-hero" data-banner-slide data-banner-inactive inert>
            <div class="market-hero-art">
                <img src="{{ asset('storage/'.$banner->image_path) }}" alt="" loading="lazy" width="1440" height="960">
            </div>
            <div class="store-wrap market-hero-inner">
                <div class="market-hero-copy">
                    <h2>{{ $banner->title }}</h2>
                    @if ($banner->description)
                        <p>{{ $banner->description }}</p>
                    @endif
                    @if ($banner->link)
                        <a class="store-button market-yellow" href="{{ $banner->link }}">Lihat selengkapnya <span aria-hidden="true">→</span></a>
                    @endif
                </div>
            </div>
        </div>
    @endforeach
    @if ($banners->isNotEmpty())
        <div class="carousel-controls" data-banner-controls hidden>
            <button type="button" data-banner-step="-1" class="carousel-arrow carousel-prev" aria-label="Banner sebelumnya"><x-store.icon name="left" /></button>
            @for ($index = 0; $index <= $banners->count(); $index++)
                <button type="button" data-banner-index="{{ $index }}" aria-label="Tampilkan banner {{ $index + 1 }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}"><span></span></button>
            @endfor
            <button type="button" data-banner-step="1" class="carousel-arrow carousel-next" aria-label="Banner berikutnya"><x-store.icon name="right" /></button>
        </div>
    @endif
</section>

