@extends('store.layout')

@section('title', 'Pre-order (PO) · '.e($setting->store_name))

@section('content')
    <div class="store-wrap store-page">
        <h1 class="mobile-section-title">Pre-order (PO)</h1>
        <div class="market-po preorder-banner">
            <x-store.icon name="calendar" class="size-10" />
            <div><h2>Program Pre-order</h2><p>Cek estimasi kesiapan sebelum memesan.</p></div>
        </div>
        <h2 class="preorder-list-title">Semua PO</h2>
        <ul class="preorder-list">
            @forelse ($products as $product)
                <li>
                    <a class="preorder-row" href="{{ route('store.product', $product->slug) }}">
                        <div class="preorder-image">
                            @if ($product->coverImage)
                                <img src="{{ asset('storage/'.$product->coverImage->path) }}" alt="" loading="lazy">
                            @else
                                <x-store.icon name="grid" />
                            @endif
                        </div>
                        <div class="min-w-0">
                            <h3 class="wrap-anywhere font-bold">{{ $product->name }}</h3>
                            <p class="font-extrabold">{{ \App\Support\Rupiah::format((int) $product->starting_price) }}</p>
                            <span class="store-po">Pre-order</span>
                            <p class="mt-1 text-xs wrap-anywhere">Estimasi siap {{ $product->preorder_estimate }}</p>
                        </div>
                        <x-store.icon name="right" class="size-4" />
                    </a>
                </li>
            @empty
                <li class="store-empty">Belum ada produk pre-order yang tersedia.</li>
            @endforelse
        </ul>
        <div class="mt-5">{{ $products->links() }}</div>
    </div>
@endsection

