<?php

namespace App\Http\Controllers\Store;

use App\Http\Controllers\Controller;
use App\Models\StoreSetting;
use App\Services\CartService;
use App\Services\WhatsAppService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index(Request $request, CartService $cart, WhatsAppService $whatsapp): View
    {
        $summary = $cart->summary($request->session());
        $setting = StoreSetting::forStorefront();

        return view('store.cart', $summary + [
            'setting' => $setting,
            'hasWhatsApp' => $whatsapp->isConfigured($setting->whatsapp_number),
            'cartCount' => $summary['count'],
        ]);
    }

    public function store(Request $request, CartService $cart): RedirectResponse
    {
        $data = $request->validate([
            'variant_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['required', 'integer', 'min:1', 'max:2147483647'],
        ], $this->messages());

        $cart->add($request->session(), (int) $data['variant_id'], (int) $data['quantity']);

        return to_route('store.cart.index')->with('status', 'Produk ditambahkan ke keranjang.');
    }

    public function update(Request $request, CartService $cart, int $item): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:2147483647'],
        ], $this->messages());

        $cart->update($request->session(), $item, (int) $data['quantity']);

        return to_route('store.cart.index')->with('status', 'Jumlah produk diperbarui.');
    }

    public function destroy(Request $request, CartService $cart, int $item): RedirectResponse
    {
        $cart->remove($request->session(), $item);

        return to_route('store.cart.index')->with('status', 'Item dihapus dari keranjang.');
    }

    public function clear(Request $request, CartService $cart): RedirectResponse
    {
        $cart->clear($request->session());

        return to_route('store.cart.index')->with('status', 'Keranjang dikosongkan.');
    }

    private function messages(): array
    {
        return [
            'variant_id.*' => 'Pilih varian produk yang tersedia.',
            'quantity.*' => 'Masukkan jumlah unit yang valid.',
        ];
    }
}
