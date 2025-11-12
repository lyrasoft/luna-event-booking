<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Payment;

use Ecpay\Sdk\Factories\Factory;
use Ecpay\Sdk\Response\VerifiedArrayResponse;
use Ecpay\Sdk\Services\CheckMacValueService;
use Ecpay\Sdk\Services\UrlService;
use Lyrasoft\EventBooking\Data\EventAttendingStore;
use Lyrasoft\EventBooking\Entity\EventOrder;
use Lyrasoft\EventBooking\Enum\EcpayPaymentType;
use Lyrasoft\EventBooking\Enum\EventOrderState;
use Lyrasoft\EventBooking\Enum\OrderHistoryType;
use Lyrasoft\EventBooking\Service\EventOrderService;
use Lyrasoft\Firewall\Service\RedirectService;
use Psr\Http\Message\UriInterface;
use Unicorn\View\ORMAwareViewModelTrait;
use Windwalker\Core\Application\AppContext;
use Windwalker\Core\Application\ApplicationInterface;
use Windwalker\Core\DateTime\ChronosService;
use Windwalker\Core\Manager\Logger;
use Windwalker\Core\Renderer\RendererService;
use Windwalker\Core\Router\Navigator;
use Windwalker\Core\Router\RouteUri;
use Windwalker\Core\Router\SystemUri;
use Windwalker\ORM\ORM;
use Windwalker\Utilities\Contract\LanguageInterface;
use Windwalker\Utilities\Str;

use function Windwalker\chronos;

class EcpayPayment implements EventPaymentInterface
{
    use PaymentTrait;
    use ORMAwareViewModelTrait;

    public function __construct(
        protected ApplicationInterface $app,
        public EcpayPaymentType $type,
        string $title = '',
        public bool $testMode = false,
    ) {
        $this->title = $title;
    }

    public function getTitle(LanguageInterface $lang): string
    {
        return $this->title ?: $this->type->getTitle($lang);
    }

    public static function getId(): string
    {
        return 'ecpay';
    }

    public static function getTypeTitle(LanguageInterface $lang): string
    {
        return '綠界支付';
    }

    public static function getDescription(LanguageInterface $lang): string
    {
        return '綠界金流 All-in-one 支付';
    }

    public function process(EventAttendingStore $store): mixed
    {
        /** @var EventOrder $order */
        $order = $store->order;

        $nav = $this->app->service(Navigator::class);
        $chronos = $this->app->service(ChronosService::class);

        $notify = $nav->to('event_payment_task')->id($order->id)
            ->full();

        $desc = [
            $store->event->title . ' - ' . $store->stage->title,
        ];

        $input = [
            'MerchantID' => $this->getMerchantID(),
            'MerchantTradeNo' => $order->transactionNo,
            'MerchantTradeDate' => $chronos->toLocalFormat('now', 'Y/m/d H:i:s'),
            'PaymentType' => 'aio',
            'TotalAmount' => (int) $order->total,
            'TradeDesc' => UrlService::ecpayUrlEncode('Shop Checkout'),
            'ItemName' => implode("#", $desc),
            'ReturnURL' => $this->replaceWebhookUrl($notify->task('receivePaid')),
            'ClientBackURL' => $notify->task('returnBack'),
            'ChoosePayment' => $this->type->value,
            'EncryptType' => 1,

            'ExpireDate' => 7,
            'PaymentInfoURL' => $this->replaceWebhookUrl($notify->task('paymentInfo')),
        ];

        if ($this->type === EcpayPaymentType::CREDIT) {
            $order->expiredAt = chronos('+10minutes');
        } else {
            $order->expiredAt = chronos('+7days');
        }

        $order->paymentParams->input = $input;

        $this->orm->updateOne($order);

        $factory = $this->getEcpayFactory();

        return $factory->create('AutoSubmitFormWithCmvService')->generate(
            $input,
            $this->getEndpoint('Cashier/AioCheckOut/V5')
        );
    }

    protected function replaceWebhookUrl(UriInterface|string $uri): string
    {
        $url = (string) $uri;
        $systemUri = $this->app->retrieve(SystemUri::class);

        if ($tunnel = env('ECPAY_WEBHOOK_URL')) {
            $url = $tunnel . Str::removeLeft($url, $systemUri->root());
        }

        return $url;
    }

    public function runTask(AppContext $app, EventOrder $order, string $task): mixed
    {
        return match ($task) {
            'receivePaid' => $app->call($this->receivePaid(...)),
            'returnBack' => $app->call($this->returnBack(...)),
            'paymentInfo' => $app->call($this->paymentInfo(...)),
        };
    }

    protected function receivePaid(AppContext $app, ORM $orm, EventOrderService $orderService): string
    {
        $factory = $this->getEcpayFactory();
        /** @var VerifiedArrayResponse $checkoutResponse */
        $checkoutResponse = $factory->create(VerifiedArrayResponse::class);

        $id = (string) $app->input('id');

        try {
            $res = $checkoutResponse->get($_POST);
        } catch (\Exception $e) {
            Logger::error('event-booking/ecpay-payment-error', 'ID: ' . $id);
            Logger::error('event-booking/ecpay-payment-error', $e->getMessage());

            return '0|' . $e->getMessage();
        }

        $order = $orm->findOne(EventOrder::class, $id);

        if (!$order) {
            Logger::error('event-booking/ecpay-payment-error', 'ID: ' . $id);
            Logger::error('event-booking/ecpay-payment-error', 'Order not found');

            return '0|No order';
        }

        $params = &$order->params;
        $params['payment_notify_error'] = null;

        try {
            if ((string) $res['RtnCode'] === '1') {
                if (!$order->paidAt) {
                    $order->paidAt = 'now';
                }

                $orderService->transition(
                    $order,
                    EventOrderState::DONE,
                    OrderHistoryType::SYSTEM,
                    '付款成功',
                    true
                );
            } else {
                $orderService->transition(
                    $order,
                    EventOrderState::FAIL,
                    OrderHistoryType::SYSTEM,
                    $res['RtnMsg'] ?? '付款失敗',
                    false
                );
            }
        } catch (\Throwable $e) {
            $params['payment_notify_error'] = $e->getMessage();

            $orm->updateBatch(
                EventOrder::class,
                compact('params'),
                ['id' => $order->id]
            );

            Logger::error('event-booking/ecpay-payment-error', "ID: $id");
            Logger::error('event-booking/ecpay-payment-error', $e->getMessage());

            return '0|' . $e->getMessage();
        }

        return '1|OK';
    }

    public function returnBack(AppContext $app, Navigator $nav): RouteUri
    {
        $id = (string) $app->input('id');
        $order = $this->orm->mustFindOne(EventOrder::class, $id);

        // $app->state->forget('checkout.data');

        return $nav->to('event_order_item')
            ->var('no', $order->no);
    }

    public function paymentInfo(AppContext $app): string
    {
        $orm = $app->service(ORM::class);

        $id = (string) $app->input('id');

        $order = $orm->mustFindOne(EventOrder::class, $id);

        $order->paymentParams->info = $app->input()->dump();

        $orm->updateOne($order);

        return '1|OK';
    }

    public function orderInfo(EventOrder $order, iterable $attends): string
    {
        $payment = $this;

        return $this->app->retrieve(RendererService::class)->render(
            'event.ecpay.ecpay-order-info',
            compact('order', 'attends', 'payment')
        );
    }

    public function getEndpoint(string $path): string
    {
        $stage = $this->isTest() ? '-stage' : '';

        return "https://payment{$stage}.ecpay.com.tw/" . $path;
    }

    public function isTest(): bool
    {
        return $this->getMerchantID() === '2000132';
    }

    public function getMerchantID(): string
    {
        return $this->getEnvCredentials()[0];
    }

    public function getHashKey(): string
    {
        return $this->getEnvCredentials()[1];
    }

    public function getHashIV(): string
    {
        return $this->getEnvCredentials()[2];
    }

    public function getEcpayFactory(string $hashMethod = CheckMacValueService::METHOD_SHA256): Factory
    {
        return new Factory(
            [
                'hashKey' => $this->getHashKey(),
                'hashIv' => $this->getHashIV(),
                'hashMethod' => $hashMethod,
            ]
        );
    }

    /**
     * @return  string[]
     */
    protected function getEnvCredentials(): array
    {
        return [
            env("EVENT_ECPAY_MERCHANT_ID", '2000132'),
            env("EVENT_ECPAY_HASH_KEY", '5294y06JbISpM5x9'),
            env("EVENT_ECPAY_HASH_IV", 'v77hoKGq4kWxNNIS'),
        ];
    }
}
