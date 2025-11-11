<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Data;

use Brick\Math\BigDecimal;
use Lyrasoft\EventBooking\Entity\EventPlan;
use Windwalker\Data\RecordInterface;
use Windwalker\Data\RecordTrait;

class EventAttendingPlan implements RecordInterface
{
    use RecordTrait;

    public function __construct(
        public EventPlan $plan,
        public int $quantity = 0,
        public BigDecimal $price,
        public BigDecimal $total,
        public array $attends = [],
        public array $attendEntities = []
    ) {
    }
}
