<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Service;

use Lyrasoft\EventBooking\Entity\EventOrder;
use Lyrasoft\EventBooking\EventBookingPackage;
use Lyrasoft\EventBooking\Payment\EventPaymentInterface;
use Lyrasoft\ShopGo\Entity\Order;
use Windwalker\Core\Application\ApplicationInterface;
use Windwalker\Data\Collection;
use Windwalker\DI\Attributes\Service;
use Windwalker\Utilities\Cache\InstanceCacheTrait;

use function Windwalker\collect;

#[Service]
class EventPaymentService
{
    use InstanceCacheTrait;

    public function __construct(protected ApplicationInterface $app, protected EventBookingPackage $eventBooking)
    {
    }

    public function getGateway(string $alias): ?EventPaymentInterface
    {
        return $this->getGateways()[$alias] ?? null;
    }

    /**
     * @return  Collection<EventPaymentInterface>
     */
    public function getGateways(bool $refresh = false): Collection
    {
        return $this->once(
            'gateways',
            function () {
                $gateways = (array) $this->eventBooking->config('payment.gateways');

                foreach ($gateways as $i => $gateway) {
                    $gateways[$i] = $this->app->resolve($gateway);
                }

                return collect($gateways);
            },
            $refresh
        );
    }

    public function createNo(EventOrder $order): string
    {
        $handler = $this->eventBooking->config('payment.no_handler');

        if (!$handler instanceof \Closure) {
            throw new \LogicException('Payment NO handler is not closure');
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
