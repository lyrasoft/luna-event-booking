<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Service;

use Lyrasoft\EventBooking\Data\EventOrderHistory;
use Lyrasoft\EventBooking\Entity\EventOrder;
use Lyrasoft\EventBooking\Enum\EventOrderState;
use Lyrasoft\EventBooking\Enum\OrderHistoryType;
use Lyrasoft\EventBooking\EventBookingPackage;
use Lyrasoft\Luna\User\UserService;
use Windwalker\Core\Application\ApplicationInterface;
use Windwalker\Core\Database\ORMAwareTrait;
use Windwalker\DI\Attributes\Service;

#[Service]
class EventOrderService
{
    use ORMAwareTrait;

    public function __construct(
        protected ApplicationInterface $app,
        protected UserService $userService,
        protected EventBookingPackage $eventBooking
    ) {
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

    public function transition(
        EventOrder|int $order,
        EventOrderState|string|null $to,
        OrderHistoryType $historyType,
        string $message = '',
        bool $notify = false
    ): ?EventOrderHistory {
        if (!$order instanceof EventOrder) {
            $order = $this->orm->mustFindOne(EventOrder::class, $order);
        }

        $hasChange = false;

        if ($to) {
            $to = EventOrderState::wrap($to);

            $hasChange = $order->state !== $to;
        }

        if (!$hasChange) {
            $to = null;
        }

        return $this->orm->transaction(
            function () use ($hasChange, $order, $to, $historyType, $message, $notify) {
                if ($hasChange && $to) {
                    $order = $this->changeState($order, $to);
                }

                if ($to || $message) {
                    $history = $this->createHistory($order, $to, $historyType, $message, $notify);

                    $this->orm->updateOne(EventOrder::class, $order);

                    return $history;
                }

                return null;
            }
        );
    }

    public function changeState(EventOrder $order, EventOrderState $to): EventOrder
    {
        $order->state = $to;

        if ($to === EventOrderState::DONE) {
            $order->paidAt = 'now';
        }

        return $order;
    }

    public function createHistory(
        EventOrder $order,
        ?EventOrderState $to,
        OrderHistoryType $historyType,
        string $message,
        bool $notify
    ): EventOrderHistory {
        $histories = $order->histories;

        $histories->unshift(
            $history = new EventOrderHistory(
                type: $historyType,
                state: $to,
                stateText: $to?->getTitle($this->lang) ?? '',
                notify: $notify,
                message: $message,
            )
        );

        if ($historyType !== OrderHistoryType::SYSTEM) {
            $user = $this->userService->getUser();

            $history->userId = $user->id;
            $history->userName = $user->name;
        }

        return $history;
    }
}
