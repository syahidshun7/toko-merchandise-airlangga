@extends('admin.layout')

@php($title = $category->exists ? 'Edit kategori' : 'Tambah kategori')
@section('title', $title)

@section('content')
    <x-admin.page :title="$title">
        <form class="admin-panel max-w-xl space-y-4 p-5 sm:p-6" method="POST"
            action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
            @csrf
            @if ($category->exists)
                @method('PATCH')
            @endif
            <x-admin.field name="name" label="Nama kategori" :value="$category->name" required maxlength="150" />
            <x-admin.toggle name="is_active" label="Kategori aktif" :checked="$category->is_active" />
            <div class="flex flex-wrap gap-2">
                <button class="admin-button admin-primary" type="submit">Simpan kategori</button>
                <a class="admin-button admin-secondary" href="{{ route('admin.categories.index') }}">Batal</a>
            </div>
        </form>
    </x-admin.page>
@endsection

