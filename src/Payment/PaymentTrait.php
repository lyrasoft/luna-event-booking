<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Payment;

use Lyrasoft\EventBooking\Entity\EventOrder;
use Windwalker\Core\Router\Navigator;
use Windwalker\Core\Router\RouteUri;
use Windwalker\DI\Attributes\Inject;

trait PaymentTrait
{
    #[Inject]
    protected Navigator $nav;

    public function getTaskEndpoint(EventOrder $order): RouteUri
    {
        return $this->nav->to('event_payment_task')->id($order->id)->full();
    }
}
