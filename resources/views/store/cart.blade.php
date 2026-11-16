@extends('store.layout')

@section('title', 'Keranjang · '.e($setting->store_name))

@section('content')
    <div class="store-wrap store-page">
        <h1>Keranjang belanja</h1>

        @if (session('status'))
            <p class="mt-5 rounded-lg bg-emerald-50 p-4 text-emerald-900" role="status">{{ session('status') }}</p>
        @endif
        @if ($errors->any())
            <div class="mt-5 rounded-lg bg-red-50 p-4 text-red-900" role="alert">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif

        @if ($lines->isEmpty())
            <div class="store-empty">
                <p>Keranjang Anda masih kosong.</p>
                <a class="store-button store-button-blue mt-4" href="{{ route('store.catalog') }}">Lihat katalog</a>
            </div>
        @else
            <div class="mt-6 grid items-start gap-6 lg:grid-cols-[minmax(0,2fr)_minmax(17rem,1fr)]">
                <section aria-label="Item keranjang">
                    <ul class="divide-y divide-slate-200 rounded-xl border border-slate-200">
                        @foreach ($lines as $line)
                            @php(['item' => $item, 'variant' => $variant, 'product' => $product, 'active' => $active, 'available' => $available, 'price' => $price, 'subtotal' => $subtotal] = $line)
                            <li class="p-4 sm:p-5">
                                <div class="flex items-start gap-4">
                                    @if ($product?->coverImage)
                                        <img class="size-20 shrink-0 rounded-lg bg-slate-100 object-cover" src="{{ asset('storage/'.$product->coverImage->path) }}" alt="" loading="lazy">
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <h2 class="font-semibold wrap-anywhere text-school-blue">
                                            @if ($active)
                                                <a class="hover:underline" href="{{ route('store.product', $product->slug) }}">{{ $product->name }}</a>
                                            @else
                                                {{ $product?->name ?? 'Produk tidak tersedia' }}
                                            @endif
                                        </h2>
                                        <p class="text-sm text-slate-600">Varian {{ ($variant?->name ?? 'tidak tersedia') }} · {{ $item->quantity }} unit</p>
                                        @if ($product?->is_preorder)
                                            <p class="text-sm font-semibold text-school-blue">Pre-order (PO) · Estimasi siap {{ $product->preorder_estimate }}</p>
                                        @endif
                                        @if ($price !== null)
                                            <p class="mt-1 text-sm">{{ \App\Support\Rupiah::format($price) }} per unit</p>
                                        @endif
                                        @if (!$available)
                                            <p class="mt-1 text-sm font-medium text-red-800">
                                                @if (!$active || $variant->stock === 0)
                                                    Item ini sudah tidak tersedia.
                                                @else
                                                    Stok tersisa {{ $variant->stock }} unit. Kurangi jumlah.
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                    @if ($subtotal !== null)
                                        <div class="text-right text-sm">
                                            <p class="text-slate-600">Subtotal</p>
                                            <p class="font-bold tabular-nums">{{ \App\Support\Rupiah::format($subtotal) }}</p>
                                        </div>
                                    @endif
                                </div>

                                <div class="mt-4 flex flex-wrap items-end gap-2">
                                    @if ($active && $product->quantityLimit($variant->stock) > 0)
                                        <form method="POST" action="{{ route('store.cart.update', $item) }}" class="flex flex-wrap items-end gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <div>
                                                <label class="block text-sm font-semibold" for="quantity-{{ $item->id }}">Jumlah</label>
                                                <input class="mt-1 h-11 w-24 rounded-lg border border-slate-400 px-3" id="quantity-{{ $item->id }}"
                                                    name="quantity" type="number" inputmode="numeric" min="1" max="{{ $product->quantityLimit($variant->stock) }}"
                                                    value="{{ min($item->quantity, $product->quantityLimit($variant->stock)) }}" required>
                                            </div>
                                            <button class="admin-button admin-secondary" type="submit">Simpan jumlah</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('store.cart.destroy', $item) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button class="admin-button admin-danger" type="submit" aria-label="Hapus {{ $product?->name ?? 'produk' }} varian {{ ($variant?->name ?? 'tidak tersedia') }}">Hapus</button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                </section>

                <aside class="rounded-xl bg-blue-50 p-5 sm:p-6">
                    <h2 class="text-xl font-semibold text-school-blue">Ringkasan belanja</h2>
                    <dl class="mt-4 space-y-3">
                        <div class="flex justify-between gap-3">
                            <dt>Jumlah unit</dt>
                            <dd class="tabular-nums">{{ number_format($count, 0, ',', '.') }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-t border-blue-200 pt-3 font-bold">
                            <dt>Total{{ $hasUnavailable ? ' item tersedia' : '' }}</dt>
                            <dd class="text-right tabular-nums">{{ \App\Support\Rupiah::format($total) }}</dd>
                        </div>
                    </dl>
                    <form method="POST" action="{{ route('store.cart.whatsapp') }}" class="mt-5">
                        @csrf
                        <button class="store-button store-button-blue w-full" type="submit"
                            @disabled(!$hasWhatsApp || $hasUnavailable)
                            @if (!$hasWhatsApp || $hasUnavailable) aria-describedby="checkout-note" @endif>
                            Pesan melalui WhatsApp
                        </button>
                        @if (!$hasWhatsApp || $hasUnavailable)
                            <p id="checkout-note" class="mt-2 text-sm text-slate-700">
                                {{ !$hasWhatsApp ? 'Nomor WhatsApp toko belum dikonfigurasi.' : 'Sesuaikan atau hapus item yang tidak tersedia untuk melanjutkan.' }}
                            </p>
                        @endif
                    </form>
                    <a class="admin-button admin-secondary mt-2 w-full" href="{{ route('store.catalog') }}">Lanjut belanja</a>
                    <form method="POST" action="{{ route('store.cart.clear') }}" class="mt-2" data-confirm="Kosongkan seluruh isi keranjang?">
                        @csrf
                        @method('DELETE')
                        <button class="admin-button admin-danger w-full" type="submit">Kosongkan keranjang</button>
                    </form>
                </aside>
            </div>
        @endif
    </div>
@endsection



