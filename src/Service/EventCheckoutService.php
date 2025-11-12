<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Service;

use Lyrasoft\EventBooking\Data\EventAttendingStore;
use Lyrasoft\EventBooking\Data\EventOrderHistory;
use Lyrasoft\EventBooking\Entity\EventAttend;
use Lyrasoft\EventBooking\Entity\EventOrder;
use Lyrasoft\EventBooking\Enum\AttendState;
use Lyrasoft\EventBooking\Enum\EventOrderState;
use Lyrasoft\EventBooking\Enum\OrderHistoryType;
use Windwalker\Core\Language\TranslatorTrait;
use Windwalker\Data\Collection;
use Windwalker\DI\Attributes\Service;
use Windwalker\ORM\ORM;

#[Service]
class EventCheckoutService
{
    use TranslatorTrait;

    public function __construct(
        protected ORM $orm,
        protected EventOrderService $orderService,
        protected EventOrderStateService $stateService,
        protected EventAttendeeService $attendeeService,
        protected EventPaymentService $paymentService,
    ) {
        //
    }

    /**
     * @param  EventAttendingStore  $store
     *
     * @return  array{ 0: EventOrder, 1: Collection<EventAttend> }
     */
    public function processOrderAndSave(EventAttendingStore $store): array
    {
        $order = $store->order ?? throw new \RuntimeException('Missing order to checkout');

        $order = $this->prepareInitialOrderState($order, $store);

        /** @var EventOrder $order */
        $order = $this->orm->createOne($order);

        $order->no = $this->orderService->createNo($order);

        $this->orm->updateOne($order);

        $attends = $store->getAllAttendEntities();

        foreach ($attends as $i => $attend) {
            $attend = $this->prepareEventAttend($order, $attend, $store);

            /** @var EventAttend $attend */
            $attend = $this->orm->createOne($attend);

            $attend->no = $this->attendeeService->createNo($order, $attend);

            $this->orm->updateOne($attend);

            $attends[$i] = $attend;
        }

        $store->order = $order;

        return [$order, $attends];
    }

    public function prepareInitialOrderState(EventOrder $order, EventAttendingStore $store): EventOrder
    {
        // Todo: Handle Alternates

        $order->state = $this->orderService->getInitialState($order);
        $order->histories
            ->unshift(
                new EventOrderHistory(
                    type: OrderHistoryType::SYSTEM,
                    state: $order->state,
                    stateText: $order->state->getTitle($this->lang),
                    notify: true,
                    message: '訂單建立',
                )
            );

        return $order;
    }

    public function prepareEventAttend(EventOrder $order, EventAttend $attend, EventAttendingStore $store): EventAttend
    {
        $attend->orderId = $order->id;
        $attend->state = $this->attendeeService->getInitialState($order, $attend);

        return $attend;
    }

    public function processPayment(EventAttendingStore $store): mixed
    {
        $order = $store->order;

        if (!$order) {
            throw new \RuntimeException('Order not found in attending store.');
        }

        $gateway = $this->paymentService->getGateway($order->payment);

        if (!$gateway) {
            throw new \RuntimeException('付款方式不可用，請聯繫管理員');
        }

        $order->transactionNo = $this->paymentService->createNo($order);
        $order->paymentParams->paymentTitle = $gateway->getTitle($this->lang);

        $this->orm->updateOne($order);

        return $gateway->process($store);
    }
}
