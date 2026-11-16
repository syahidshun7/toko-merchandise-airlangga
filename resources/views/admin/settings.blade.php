@extends('admin.layout')

@section('title', 'Pengaturan toko')

@section('content')
    <x-admin.page title="Pengaturan toko">
        <form class="admin-panel max-w-3xl space-y-5 p-5 sm:p-6" method="POST" enctype="multipart/form-data" action="{{ route('admin.settings.update') }}">
            @csrf
            @method('PATCH')
            <div class="grid gap-5 sm:grid-cols-2">
                <x-admin.field name="store_name" label="Nama toko" :value="$setting->store_name" required maxlength="150" />
                <x-admin.field name="school_name" label="Nama sekolah" :value="$setting->school_name" required maxlength="150" />
            </div>
            <x-admin.field name="whatsapp_number" label="Nomor WhatsApp" type="tel" :value="$setting->whatsapp_number"
                hint="Contoh 0812 3456 7890 atau +62 812 3456 7890." maxlength="30" />
            <x-admin.field name="address" label="Alamat sekolah" type="textarea" :value="$setting->address" rows="2" maxlength="1000" />
            <x-admin.field name="description" label="Deskripsi toko" type="textarea" :value="$setting->description" rows="4" maxlength="5000" />
            @if ($setting->logo_path)
                <div>
                    <img class="max-h-24 max-w-48 object-contain" src="{{ asset('storage/'.$setting->logo_path) }}" alt="Logo toko saat ini">
                    <x-admin.toggle name="remove_logo" label="Hapus logo saat ini" />
                </div>
            @endif
            <x-admin.field name="logo" label="Logo toko" type="file" accept="image/jpeg,image/png,image/webp" hint="JPG, PNG, atau WebP. Maksimal 2 MB." />
            <button class="admin-button admin-primary" type="submit">Simpan pengaturan</button>
        </form>
    </x-admin.page>
@endsection


