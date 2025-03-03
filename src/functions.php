<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking {

    use Brick\Math\BigNumber;

    function numberFormat(mixed $num, string $prefix = '', int $decimals = 0): string
    {
        if ($num instanceof BigNumber) {
            $num = $num->toFloat();
        }

        if (!is_numeric($num)) {
            return '';
        }

        $n = (float) $num;

        $negative = $n < 0;

        $price = $prefix . number_format(abs($n), $decimals);

        return $negative ? '-' . $price : $price;
    }
}
