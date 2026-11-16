@extends('admin.layout')
@section('title', 'Banner')

@section('content')
    <x-admin.page title="Banner">
        <x-slot:actions>
            <a class="admin-button admin-primary" href="{{ route('admin.banners.create') }}">Tambah banner</a>
        </x-slot:actions>
        <ul class="admin-panel divide-y divide-slate-200">
            @forelse ($banners as $banner)
                <li class="flex flex-wrap items-center justify-between gap-3 p-4">
                    <div class="flex min-w-0 items-center gap-4">
                        <img class="h-16 w-24 shrink-0 rounded-md object-cover" src="{{ asset('storage/'.$banner->image_path) }}" alt="" loading="lazy">
                        <div class="min-w-0">
                            <p class="wrap-anywhere font-semibold">{{ $banner->title }}</p>
                            <p class="text-sm text-slate-600">{{ $banner->is_active ? 'Aktif' : 'Nonaktif' }} · Urutan {{ $banner->sort_order }}</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <a class="admin-button admin-secondary" href="{{ route('admin.banners.edit', $banner) }}">Edit</a>
                        <x-admin.delete :action="route('admin.banners.destroy', $banner)" message="Hapus banner ini?" />
                    </div>
                </li>
            @empty
                <li class="p-4 text-slate-600">Belum ada banner. Beranda menampilkan banner bawaan.</li>
            @endforelse
        </ul>
        <div class="mt-4">{{ $banners->links() }}</div>
    </x-admin.page>
@endsection

