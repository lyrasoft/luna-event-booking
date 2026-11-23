<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Module\Front\EventAttending;

use Lyrasoft\EventBooking\Data\EventAttendingStore;
use Lyrasoft\EventBooking\Entity\EventAttend;
use Lyrasoft\EventBooking\Entity\EventOrder;
use Lyrasoft\EventBooking\Enum\InvoiceType;
use Lyrasoft\EventBooking\Module\Front\EventAttending\Form\EventAttendingForm;
use Lyrasoft\EventBooking\Service\EventAttendingService;
use Lyrasoft\EventBooking\Service\EventCheckoutService;
use Lyrasoft\EventBooking\Service\EventOrderService;
use Lyrasoft\EventBooking\Service\EventPaymentService;
use Lyrasoft\EventBooking\Service\EventViewService;
use Lyrasoft\Luna\User\UserService;
use Windwalker\Core\Application\AppContext;
use Windwalker\Core\Attributes\Controller;
use Windwalker\Core\Form\FormFactory;
use Windwalker\Core\Manager\Logger;
use Windwalker\Filter\Rule\EmailAddress;
use Windwalker\Form\Form;
use Windwalker\Http\HttpClient;
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
        EventOrderService $eventOrderService,
        FormFactory $formFactory
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

        $orderForm = $this->createOrderForm($formFactory, $order);

        if (!$orderForm || !$this->validateCheckoutData($orderForm, $order, $attends)) {
            $app->addMessage('報名資料格式不正確，請重新確認', 'warning');

            return $app->getNav()->to('event_attending')->var('stageId', $stage->id);
        }

        $order = $orderForm->filter(['order' => $order])['order'];

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

                $stage = $store->stage;

                $user = $userService->getUser();

                // Order
                $orderData = $store->orderData;

                $order = new EventOrder();

                if ($user->isLogin()) {
                    $order->userId = (int) $user->id;
                }

                $order->eventId = $event->id;
                $order->stageId = $stage->id;
                $order->invoiceType = $orderData['invoice_type'];
                $order->invoiceData = $orderData['invoice_data'];
                $order->total = $store->getGrandTotal()->toFloat();
                $order->totals = clone $store->totals;
                $order->name = $orderData['name'] ?? '';
                $order->email = $orderData['email'] ?? '';
                $order->nick = $orderData['nick'] ?? '';
                $order->mobile = $orderData['mobile'] ?? '';
                $order->phone = $orderData['phone'] ?? '';
                $order->address = $orderData['address'] ?? '';
                $order->details = $orderData['details'] ?? [];
                $order->payment = $orderData['payment'] ?? '';
                $order->paymentData = $orderData['payment_data'] ?? [];
                $order->snapshots = compact('event', 'stage');
                $order->attends = count($store->getAllAttends());

                $store->order = $order;

                // Prepare Attends
                foreach ($store->attendingPlans as $attendingPlan) {
                    $attends = $attendingPlan->attends;
                    $attendEntities = [];
                    $plan = $attendingPlan->plan;

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
                        $attend->snapshots = [
                            'plan' => $plan,
                        ];

                        $attendEntities[] = $attend;
                    }

                    $attendingPlan->attendEntities = $attendEntities;
                }

                $eventCheckoutService->processOrderAndSave($store);

                return $store;
            }
        );

        // Todo: Event

        $order = $store->order;
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

    protected function createOrderForm(FormFactory $formFactory, mixed $order): ?Form
    {
        if (!is_array($order) || !$this->isStringTree($order)) {
            return null;
        }

        $invoiceType = $order['invoice_type'] ?? null;

        if (!is_string($invoiceType) || !InvoiceType::tryFrom($invoiceType)) {
            return null;
        }

        $form = $formFactory->create(EventAttendingForm::class);

        // Conditional fields: only keep the ones matching the invoice type
        if ($invoiceType === InvoiceType::BUSINESS->value) {
            $form->removeField('order/invoice_data/carrier_code');
        } else {
            $form->removeField('order/invoice_data/title');
            $form->removeField('order/invoice_data/vat');
        }

        return $form;
    }

    protected function validateCheckoutData(Form $form, mixed $order, mixed $attends): bool
    {
        if (!is_array($attends) || !$this->isStringTree($attends)) {
            return false;
        }

        if ($form->validate(['order' => $order])->isFailure()) {
            return false;
        }

        $emailValidator = new EmailAddress();

        foreach ($attends as $planAttends) {
            if (!is_array($planAttends)) {
                return false;
            }

            foreach ($planAttends as $attend) {
                if (!is_array($attend)) {
                    return false;
                }

                if (
                    trim((string) ($attend['name'] ?? '')) === ''
                    || !$emailValidator->test((string) ($attend['email'] ?? ''))
                    || preg_match('/^09\d{8}$/', (string) ($attend['mobile'] ?? '')) !== 1
                ) {
                    return false;
                }
            }
        }

        return true;
    }

    protected function isStringTree(mixed $value): bool
    {
        if (is_string($value)) {
            return true;
        }

        if (!is_array($value)) {
            return false;
        }

        foreach ($value as $item) {
            if (!$this->isStringTree($item)) {
                return false;
            }
        }

        return true;
    }

    public function paymentTask(AppContext $app, ORM $orm, EventPaymentService $paymentService)
    {
        $id = $app->input('id');
        $task = $app->input('task');

        Logger::info('event-booking/payment-task', $uri = $app->getSystemUri()->full());
        Logger::info('event-booking/payment-task', print_r($app->input()->dump(), true));

        try {
            $order = $orm->findOne(EventOrder::class, $id);

            if (!$order) {
                throw new \RuntimeException('Order not found.');
            }

            $gateway = $paymentService->getGateway($order->payment);

            if (!$gateway) {
                throw new \RuntimeException('Gateway not found.');
            }

            $http = new HttpClient();
            Logger::info(
                'event-booking/payment-task',
                $http->toCurlCmd('POST', $uri, HttpClient::formData($app->input()->dump()))
            );

            return $gateway->runTask($app, $order, $task);
        } catch (\Throwable $e) {
            Logger::info('event-booking/payment-error', $e);

            return $e->getMessage();
        }
    }
}
