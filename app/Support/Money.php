<?php

namespace App\Support;

class Money
{
    public static function format(string $amount): string
    {
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '00');

        return number_format((int) $whole, 0, ',', '.').','.str_pad($fraction, 2, '0');
    }
}
