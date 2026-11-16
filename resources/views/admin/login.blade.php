@extends('admin.layout')

@section('title', 'Masuk admin')

@section('content')
    <main class="mx-auto max-w-lg px-5 py-10 sm:px-8 sm:py-20">
        <section aria-labelledby="login-heading" class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-9">
            <h1 id="login-heading" class="text-3xl font-semibold tracking-tight text-school-blue">Masuk admin</h1>
            <p class="mt-2 text-base leading-relaxed text-slate-600">Gunakan akun admin untuk mengelola toko merchandise sekolah.</p>

            <form method="POST" action="{{ route('admin.login.store') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold">Email</label>
                    <input id="email" name="email" type="email" value="{{ is_string(old('email')) ? old('email') : '' }}"
                        autocomplete="username" autocapitalize="none" spellcheck="false" maxlength="255" required
                        @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                        class="mt-2 block min-h-12 w-full rounded-lg border border-slate-400 bg-white px-3 py-2.5 text-base focus:border-school-blue focus:outline-2 focus:outline-offset-2 focus:outline-school-blue aria-invalid:border-red-700">
                    @error('email')
                        <p id="email-error" class="mt-2 text-sm text-red-800">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-semibold">Kata sandi</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" maxlength="255" required
                        @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                        class="mt-2 block min-h-12 w-full rounded-lg border border-slate-400 bg-white px-3 py-2.5 text-base focus:border-school-blue focus:outline-2 focus:outline-offset-2 focus:outline-school-blue aria-invalid:border-red-700">
                    @error('password')
                        <p id="password-error" class="mt-2 text-sm text-red-800">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="min-h-12 w-full cursor-pointer rounded-lg bg-school-blue px-4 py-2.5 font-semibold text-white transition-colors hover:bg-blue-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-school-blue motion-reduce:transition-none">
                    Masuk
                </button>
            </form>
        </section>
    </main>
@endsection

