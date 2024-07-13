<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Payment;

use Lyrasoft\EventBooking\Data\EventAttendingStore;
use Lyrasoft\EventBooking\Entity\EventOrder;
use Windwalker\Core\Application\AppContext;
use Windwalker\Utilities\Contract\LanguageInterface;

interface EventPaymentInterface
{
    public static function getId(): string;

    public static function getTitle(LanguageInterface $lang): string;

    public static function getDescription(LanguageInterface $lang): string;

    public function process(EventAttendingStore $store): mixed;

    public function orderInfo(EventOrder $order, iterable $attends): string;

    public function runTask(AppContext $app, EventOrder $order): mixed;

    public function createTransactionNo(EventOrder $order): string;
}
