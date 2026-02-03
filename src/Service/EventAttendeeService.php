<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Service;

use Lyrasoft\EventBooking\Entity\EventAttend;
use Lyrasoft\EventBooking\Entity\EventOrder;
use Lyrasoft\EventBooking\Entity\EventStage;
use Lyrasoft\EventBooking\Enum\AttendState;
use Lyrasoft\EventBooking\EventBookingPackage;
use Lyrasoft\Luna\Entity\User;
use Windwalker\Core\Application\ApplicationInterface;
use Windwalker\Core\Database\ORMAwareTrait;
use Windwalker\DI\Attributes\Service;
use Windwalker\Query\Query;

#[Service]
class EventAttendeeService
{
    use ORMAwareTrait;

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

    public function getUserAttend(EventStage|int $eventStage, User|int $user, ?AttendState $state = null): ?EventAttend
    {
        $eventStageId = $eventStage instanceof EventStage ? $eventStage->id : $eventStage;
        $userId = $user instanceof User ? $user->id : $user;

        /** @var ?EventAttend $attend */
        $attend = $this->orm->from(EventAttend::class)
            ->where('stage_id', $eventStageId)
            ->where('user_id', $userId)
            ->tapIf(
                (bool) $state,
                fn (Query $query) => $query->where('state', $state)
            )
            ->get(EventAttend::class);

        return $attend;
    }
}
