@extends('admin.layout')

@section('title', 'Kategori')

@section('content')
    <x-admin.page title="Kategori">
        <x-slot:actions>
            <a class="admin-button admin-primary" href="{{ route('admin.categories.create') }}">Tambah kategori</a>
        </x-slot:actions>

        <ul class="admin-panel divide-y divide-slate-200">
            @forelse ($categories as $category)
                <li class="flex flex-wrap items-center justify-between gap-3 p-4 sm:px-5">
                    <div class="min-w-0">
                        <p class="wrap-break-word font-semibold">{{ $category->name }}</p>
                        <p class="mt-1 text-sm text-slate-600">{{ $category->is_active ? 'Aktif' : 'Nonaktif' }} · {{ $category->products_count }} produk</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <a class="admin-button admin-secondary" href="{{ route('admin.categories.edit', $category) }}">Edit</a>
                        <x-admin.delete :action="route('admin.categories.destroy', $category)" :message="'Hapus kategori '.$category->name.'?'" />
                    </div>
                </li>
            @empty
                <li class="p-5 text-slate-600">Belum ada kategori.</li>
            @endforelse
        </ul>
        <div class="mt-5">{{ $categories->links() }}</div>
    </x-admin.page>
@endsection

