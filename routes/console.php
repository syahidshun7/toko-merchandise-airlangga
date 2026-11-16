<?php

use App\Models\Cart;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('cart:prune', function () {
    $deleted = Cart::query()->where('expires_at', '<=', now())->delete();
    $this->info("Keranjang kedaluwarsa dihapus: {$deleted}.");
})->purpose('Hapus keranjang yang kedaluwarsa')->hourly();
