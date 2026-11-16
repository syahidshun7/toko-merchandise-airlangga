@props(['title', 'description' => null])

<main class="mx-auto max-w-6xl px-5 py-8 sm:px-8 sm:py-10">
    <header class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div class="min-w-0">
            <h1 class="wrap-break-word text-3xl font-semibold tracking-tight text-school-blue">{{ $title }}</h1>
            @if ($description)
                <p class="mt-2 text-slate-600">{{ $description }}</p>
            @endif
        </div>
        {{ $actions ?? '' }}
    </header>

    @if (session('status'))
        <p role="status" class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-green-900">{{ session('status') }}</p>
    @endif

    @if ($errors->any())
        <div role="alert" class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-900">
            <p class="font-semibold">Periksa data berikut.</p>
            <ul class="mt-1 list-inside list-disc text-sm">
                @foreach (array_unique($errors->all()) as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{ $slot }}
</main>

