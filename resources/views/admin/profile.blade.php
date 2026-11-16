@extends('admin.layout')

@section('title', 'Profil admin')

@section('content')
    <x-admin.page title="Profil admin">
        <form class="admin-panel max-w-2xl space-y-5 p-5 sm:p-6" method="POST" action="{{ route('admin.profile.update') }}">
            @csrf
            @method('PATCH')
            <x-admin.field name="name" label="Nama" :value="$admin->name" required maxlength="150" autocomplete="name" />
            <x-admin.field name="email" label="Email" type="email" :value="$admin->email" required maxlength="255" autocomplete="username" />
            <fieldset class="space-y-4 border-t border-slate-200 pt-4">
                <legend class="pr-3 font-semibold">Ubah kata sandi</legend>
                <p class="text-sm text-slate-600">Kosongkan bagian ini jika hanya ingin mengubah nama atau email.</p>
                <x-admin.field name="current_password" label="Kata sandi saat ini" type="password" autocomplete="current-password" />
                <x-admin.field name="password" label="Kata sandi baru" type="password" minlength="12" autocomplete="new-password" />
                <x-admin.field name="password_confirmation" label="Konfirmasi kata sandi baru" type="password" autocomplete="new-password" />
            </fieldset>
            <button class="admin-button admin-primary" type="submit">Simpan profil</button>
        </form>
    </x-admin.page>
@endsection

