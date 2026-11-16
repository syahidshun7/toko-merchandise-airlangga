<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use App\Services\CartService;
use App\Services\WhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WhatsAppController extends Controller
{
    public function __invoke(Request $request, CartService $cart, WhatsAppService $whatsapp): RedirectResponse
    {
        return DB::transaction(function () use ($request, $cart, $whatsapp) {
            $summary = $cart->summary($request->session());

            if ($summary['lines']->isEmpty()) {
                return to_route('store.cart.index')->withErrors(['checkout' => 'Keranjang kosong atau sudah kedaluwarsa.']);
            }

            if ($summary['hasUnavailable']) {
                return to_route('store.cart.index')->withErrors([
                    'checkout' => 'Stok atau ketersediaan produk berubah. Periksa isi keranjang sebelum memesan.',
                ]);
            }

            $number = StoreSetting::forStorefront()->whatsapp_number;

            if (! $whatsapp->isConfigured($number)) {
                return to_route('store.cart.index')->withErrors(['checkout' => 'Nomor WhatsApp toko belum dikonfigurasi.']);
            }

            return redirect()->away($whatsapp->orderUrl($number, $summary), 303);
        });
    }
}
