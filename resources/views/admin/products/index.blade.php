@extends('admin.layout')

@section('title', 'Produk')

@section('content')
    <x-admin.page title="Produk">
        <x-slot:actions>
            <a class="admin-button admin-primary" href="{{ route('admin.products.create') }}">Tambah produk</a>
        </x-slot:actions>

        <ul class="admin-panel divide-y divide-slate-200">
            @forelse ($products as $product)
                <li class="grid items-center gap-3 p-4 sm:grid-cols-[minmax(0,1fr)_auto_auto] sm:gap-6 sm:px-5">
                    <div class="min-w-0">
                        <a class="admin-link wrap-break-word" href="{{ route('admin.products.show', $product) }}">{{ $product->name }}</a>
                        <p class="mt-1 text-sm text-slate-600">{{ $product->category->name }} · {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}{{ $product->is_featured ? ' · Unggulan' : '' }}</p>
                    </div>
                    <div class="text-sm tabular-nums text-slate-600">
                        <p>Rp{{ number_format($product->base_price, 0, ',', '.') }}</p>
                        <p>Stok {{ $product->variants_sum_stock ?? 0 }} unit</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <a class="admin-button admin-secondary" href="{{ route('admin.products.edit', $product) }}">Edit</a>
                        <x-admin.delete :action="route('admin.products.destroy', $product)" :message="'Arsipkan produk '.$product->name.'?'" label="Arsipkan" />
                    </div>
                </li>
            @empty
                <li class="p-5 text-slate-600">Belum ada produk.</li>
            @endforelse
        </ul>
        <div class="mt-5">{{ $products->links() }}</div>
    </x-admin.page>
@endsection

