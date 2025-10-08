<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Module\Front\EventAttending;

use Lyrasoft\EventBooking\Data\EventAttendingStore;
use Lyrasoft\EventBooking\Entity\EventAttend;
use Lyrasoft\EventBooking\Entity\EventOrder;
use Lyrasoft\EventBooking\Service\EventAttendingService;
use Lyrasoft\EventBooking\Service\EventCheckoutService;
use Lyrasoft\EventBooking\Service\EventOrderService;
use Lyrasoft\EventBooking\Service\EventPaymentService;
use Lyrasoft\EventBooking\Service\EventViewService;
use Lyrasoft\Luna\User\UserService;
use Windwalker\Core\Application\AppContext;
use Windwalker\Core\Attributes\Controller;
use Windwalker\Core\Manager\Logger;
use Windwalker\ORM\ORM;

use function Windwalker\response;

#[Controller]
class EventAttendingController
{
    public function attending(
        AppContext $app,
        ORM $orm,
        EventAttendingService $eventAttendingService,
        EventViewService $eventViewService
    ): mixed {
        $stageId = (int) $app->input('stageId');

        if (!$stageId) {
            return response()->redirect($app->getSystemUri()->root());
        }

        [$event, $stage] = $eventViewService->checkStageAvailableById($stageId);

        $quantity = $app->input('quantity') ?? $eventAttendingService->getPlansAndQuantity($stageId);

        $eventAttendingService->rememberAttendingData(
            $stageId,
            [
                'quantity' => $quantity,
                'attends' => [],
            ]
        );

        $storage = $eventAttendingService->getAttendingStore($stage);

        // Is empty
        if ($storage->getTotalQuantity() === 0) {
            $app->addMessage('沒有報名資訊', 'warning');

            return $app->getNav()->back();
        }

        return $app->getNav()->to('event_attending')->var('stageId', $stage->id);
    }

    public function checkout(
        AppContext $app,
        ORM $orm,
        UserService $userService,
        EventAttendingService $eventAttendingService,
        EventViewService $eventViewService,
        EventCheckoutService $eventCheckoutService,
        EventOrderService $eventOrderService
    ) {
        [$stageId, $order, $attends] = $app->input(
            'stageId',
            'order',
            'attends'
        )->values();

        [$event, $stage] = $eventViewService->checkStageAvailableById((int) $stageId);

        // Save to session
        $data = $eventAttendingService->getAttendingDataFromSession($stage->id);

        if ($data === null) {
            return response()->redirect($app->getSystemUri()->root());
        }

        $data['order'] = $order;
        $data['attends'] = $attends;

        $eventAttendingService->rememberAttendingData($stage->id, $data);

        /** @var EventAttendingStore $store */
        $store = $orm->transaction(
            function () use (
                $stage,
                $event,
                $orm,
                $userService,
                $eventAttendingService,
                $eventCheckoutService
            ) {
                $store = $eventAttendingService->getAttendingStore($stage->id, true);

                $stage = $store->getStage();

                $user = $userService->getUser();

                // Order
                $orderData = $store->getOrderData();

                $order = new EventOrder();

                if ($user->isLogin()) {
                    $order->userId = (int) $user->id;
                }

                $order->eventId = $event->id;
                $order->stageId = $stage->id;
                $order->invoiceType = $orderData['invoice_type'];
                $order->invoiceData = $orderData['invoice_data'];
                $order->total = $store->getGrandTotal()->toFloat();
                $order->totals = clone $store->getTotals();
                $order->name = $orderData['name'] ?? '';
                $order->email = $orderData['email'] ?? '';
                $order->nick = $orderData['nick'] ?? '';
                $order->mobile = $orderData['mobile'] ?? '';
                $order->phone = $orderData['phone'] ?? '';
                $order->address = $orderData['address'] ?? '';
                $order->details = $orderData['details'] ?? [];
                $order->payment = 'atm';
                $order->screenshots = compact('event', 'stage');
                $order->attends = count($store->getAllAttends());

                $store->setOrder($order);

                // Prepare Attends
                foreach ($store->getAttendingPlans() as $attendingPlan) {
                    $attends = $attendingPlan->getAttends();
                    $attendEntities = [];
                    $plan = $attendingPlan->getPlan();

                    foreach ($attends as $attendData) {
                        $attend = new EventAttend();
                        $attend->eventId = $event->id;
                        $attend->stageId = $stage->id;
                        $attend->planId = $plan->id;
                        $attend->planTitle = $plan->title;
                        $attend->price = $plan->price;
                        $attend->name = $attendData['name'] ?? '';
                        $attend->email = $attendData['email'] ?? '';
                        $attend->nick = $attendData['nick'] ?? '';
                        $attend->mobile = $attendData['mobile'] ?? '';
                        $attend->phone = $attendData['phone'] ?? '';
                        $attend->address = $attendData['address'] ?? '';
                        $attend->details = $attendData['details'] ?? [];
                        $attend->screenshots = [
                            'plan' => $plan,
                        ];

                        $attendEntities[] = $attend;
                    }

                    $attendingPlan->setAttendEntities($attendEntities);
                }

                $eventCheckoutService->processOrderAndSave($store);

                return $store;
            }
        );

        // Todo: Event

        $order = $store->getOrder();
        $orderUri = $app->getNav()->to('event_order_item')->var('no', $order->no);

        try {
            $result = $eventCheckoutService->processPayment($store);

            return $result ?: $orderUri;
        } catch (\Exception $e) {
            $app->addMessage($e->getMessage(), 'warning');
            Logger::error('checkout-error', $e->getMessage(), ['exception' => $e]);

            return $orderUri;
        }
    }

    public function paymentTask(AppContext $app, ORM $orm, EventPaymentService $paymentService)
    {
        $id = $app->input('id');

        $order = $orm->findOne(EventOrder::class, $id);

        if (!$order) {
            return 'Order not found';
        }

        $gateway = $paymentService->getGateway($order->payment);

        if ($gateway) {
            return 'Gateway not found.';
        }

        return $gateway->runTask($app, $order);
    }
}
