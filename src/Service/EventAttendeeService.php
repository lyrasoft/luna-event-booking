<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Service;

use Lyrasoft\EventBooking\Entity\EventAttend;
use Lyrasoft\EventBooking\Entity\EventOrder;
use Lyrasoft\EventBooking\Enum\AttendState;
use Lyrasoft\EventBooking\EventBookingPackage;
use Windwalker\Core\Application\ApplicationInterface;
use Windwalker\DI\Attributes\Service;

#[Service]
class EventAttendeeService
{
    public function __construct(protected ApplicationInterface $app, protected EventBookingPackage $eventBooking)
    {
    }

    public function createNo(EventOrder $order, EventAttend $attend): string
    {
        $handler = $this->eventBooking->config('attends.no_handler');

        if (!$handler instanceof \Closure) {
            throw new \LogicException('Attend NO handler is not closure');
        }

        return $this->app->call(
            $handler,
            [
                'order' => $order,
                'attend' => $attend,
                EventOrder::class => $order,
                EventAttend::class => $attend,
            ]
        );
    }

    public function getInitialState(EventOrder $order, EventAttend $attend): AttendState|string
    {
        $handler = $this->eventBooking->config('attends.initial_state');

        if (!$handler instanceof \Closure) {
            return $handler;
        }

        return $this->app->call(
            $handler,
            [
                'order' => $order,
                'attend' => $attend,
                EventOrder::class => $order,
                EventAttend::class => $attend,
            ]
        );
    }
}
