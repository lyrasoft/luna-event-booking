<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Service;

use Lyrasoft\EventBooking\Entity\EventOrder;
use Lyrasoft\EventBooking\EventBookingPackage;
use Windwalker\Core\Application\ApplicationInterface;
use Windwalker\DI\Attributes\Service;

#[Service]
class InvoiceService
{
    public function __construct(protected ApplicationInterface $app, protected EventBookingPackage $eventBooking)
    {
    }

    public function createNo(EventOrder $order): string
    {
        $invoiceNoHandler = $this->eventBooking->config('invoice.no_handler');

        if (!$invoiceNoHandler instanceof \Closure) {
            throw new \LogicException('Invoice NO handler is not closure');
        }

        return $this->app->call(
            $invoiceNoHandler,
            [
                'order' => $order,
                EventOrder::class => $order,
            ]
        );
    }
}
