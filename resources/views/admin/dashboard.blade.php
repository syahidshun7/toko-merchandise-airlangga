@extends('admin.layout')

@section('title', 'Dashboard admin')

@section('content')
    <x-admin.page title="Dashboard admin" :description="'Selamat datang, '.auth()->user()->name.'.'">
        <x-slot:actions>
            <a class="admin-button admin-primary" href="{{ route('admin.products.create') }}">Tambah produk</a>
        </x-slot:actions>

        <dl class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-6">
            @foreach ($stats as $label => $total)
                <div class="admin-panel px-4 py-4">
                    <dt class="text-sm font-semibold text-slate-600">{{ $label }}</dt>
                    <dd class="mt-2 text-3xl font-semibold tabular-nums text-school-blue">{{ number_format($total, 0, ',', '.') }}</dd>
                    @if ($label === 'Stok menipis')
                        <p class="mt-1 text-sm text-slate-600">1–{{ $threshold }} unit per produk</p>
                    @endif
                </div>
            @endforeach
        </dl>

        <section aria-labelledby="latest-heading" class="admin-panel mt-7">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 px-5 py-4">
                <h2 id="latest-heading" class="text-lg font-semibold">Produk terbaru</h2>
                <a class="admin-link" href="{{ route('admin.products.index') }}">Lihat semua produk</a>
            </div>
            <ul class="divide-y divide-slate-200">
                @forelse ($latest as $product)
                    <li class="flex flex-wrap items-center justify-between gap-x-4 gap-y-1 px-5 py-3">
                        <div class="min-w-0">
                            <a class="admin-link wrap-break-word" href="{{ route('admin.products.show', $product) }}">{{ $product->name }}</a>
                            <p class="text-sm text-slate-600">{{ $product->category->name }}</p>
                        </div>
                        <span class="text-sm tabular-nums text-slate-600">{{ $product->variants_sum_stock ?? 0 }} unit</span>
                    </li>
                @empty
                    <li class="px-5 py-5 text-slate-600">Belum ada produk.</li>
                @endforelse
            </ul>
        </section>
    </x-admin.page>
@endsection

