<?php

namespace App\Services;

use App\Support\Rupiah;
use Illuminate\Support\Str;

class WhatsAppService
{
    public function isConfigured(?string $number): bool
    {
        return $number !== null && preg_match('/^[1-9][0-9]{7,14}$/D', $number) === 1;
    }

    public function orderUrl(string $number, array $summary): string
    {
        $message = ['Halo, saya ingin membeli merchandise sekolah berikut:', ''];

        foreach ($summary['lines'] as $index => $line) {
            $message[] = ($index + 1).'. '.Str::squish($line['product']->name);
            $message[] = '   Varian: '.Str::squish($line['variant']->name);
            if ($line['product']->is_preorder) {
                $message[] = '   Pre-order (PO) · Estimasi siap: '.Str::squish($line['product']->preorder_estimate);
            }
            $message[] = '   Jumlah: '.$line['item']->quantity;
            $message[] = '   Harga: '.Rupiah::format($line['price']);
            $message[] = '   Subtotal: '.Rupiah::format($line['subtotal']);
            $message[] = '';
        }

        $message[] = '--------------------------------';
        $message[] = 'TOTAL: '.Rupiah::format($summary['total']);
        $message[] = '';
        $message[] = 'Mohon informasi lebih lanjut mengenai produk tersebut.';
        $message[] = '';
        $message[] = 'Terima kasih.';

        return 'https://wa.me/'.$number.'?text='.rawurlencode(implode("\n", $message));
    }
}
