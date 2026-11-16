<?php

namespace App\Support;

use Brick\Math\BigInteger;

class Rupiah
{
    public static function format(int|BigInteger $amount): string
    {
        return 'Rp'.preg_replace('/\B(?=(\d{3})+(?!\d))/', '.', (string) $amount);
    }
}
