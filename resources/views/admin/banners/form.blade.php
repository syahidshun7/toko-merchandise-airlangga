@extends('admin.layout')
@php($title = $banner->exists ? 'Edit banner' : 'Tambah banner')
@section('title', $title)

@section('content')
    <x-admin.page :title="$title">
        <form class="admin-panel max-w-2xl space-y-4 p-5" method="POST" enctype="multipart/form-data"
            action="{{ $banner->exists ? route('admin.banners.update', $banner) : route('admin.banners.store') }}">
            @csrf
            @if ($banner->exists)
                @method('PATCH')
                <img class="max-h-48 rounded-lg object-contain" src="{{ asset('storage/'.$banner->image_path) }}" alt="Gambar banner saat ini">
            @endif
            <x-admin.field name="title" label="Judul" :value="$banner->title" maxlength="120" required />
            <x-admin.field name="description" label="Deskripsi" :value="$banner->description" maxlength="240" />
            <x-admin.field name="image" label="Gambar" type="file" accept="image/jpeg,image/png,image/webp"
                :required="!$banner->exists" hint="JPG, PNG, atau WebP hingga 4 MB. Gunakan gambar lebar." />
            <x-admin.field name="link" label="Tautan tujuan" type="url" :value="$banner->link" maxlength="500" hint="Opsional. Gunakan tautan HTTP atau HTTPS." />
            <x-admin.field name="sort_order" label="Urutan" type="number" :value="$banner->sort_order" min="0" max="10000" required />
            <x-admin.toggle name="is_active" label="Tampilkan di beranda" :checked="$banner->is_active" />
            <button class="admin-button admin-primary" type="submit">Simpan banner</button>
            <a class="admin-button admin-secondary" href="{{ route('admin.banners.index') }}">Batal</a>
        </form>
    </x-admin.page>
@endsection

