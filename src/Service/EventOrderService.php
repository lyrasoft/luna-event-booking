<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Service;

use Lyrasoft\EventBooking\Entity\EventOrder;
use Lyrasoft\EventBooking\Enum\EventOrderState;
use Lyrasoft\EventBooking\EventBookingPackage;
use Windwalker\Core\Application\ApplicationInterface;
use Windwalker\DI\Attributes\Service;

#[Service]
class EventOrderService
{
    public function __construct(protected ApplicationInterface $app, protected EventBookingPackage $eventBooking)
    {
    }

    public function createNo(EventOrder $order): string
    {
        $handler = $this->eventBooking->config('order.no_handler');

        if (!$handler instanceof \Closure) {
            throw new \LogicException('Order NO handler is not closure');
        }

        return $this->app->call(
            $handler,
            [
                'order' => $order,
                EventOrder::class => $order,
            ]
        );
    }

    public function getInitialState(EventOrder $order): EventOrderState|string
    {
        $handler = $this->eventBooking->config('order.initial_state');

        if (!$handler instanceof \Closure) {
            return $handler;
        }

        return $this->app->call(
            $handler,
            [
                'order' => $order,
                EventOrder::class => $order,
            ]
        );
    }
}
