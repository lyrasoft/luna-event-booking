<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Service;

use Lyrasoft\EventBooking\EventBookingPackage;
use Windwalker\Core\Application\ApplicationInterface;
use Windwalker\DI\Attributes\Service;

#[Service]
class PriceFormatter
{
    public function __construct(protected EventBookingPackage $eventBooking, protected ApplicationInterface $app)
    {
    }

    public function format(mixed $price)
    {
        $formatter = $this->getPriceFormatter();

        return $this->app->call(
            $formatter,
            [
                0 => $price,
                'price' => $price,
            ]
        );
    }

    public function getPriceFormatter(): \Closure
    {
        return $this->eventBooking->config('price_formatter');
    }
}
