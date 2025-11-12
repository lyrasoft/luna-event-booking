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

    public BigDecimal $total;

    public BigDecimal $price {
        set(mixed $value) {
            $this->price = BigDecimal::of($value);

            $this->calcTotal();
        }
    }

    public int $quantity = 0 {
        set {
            $this->quantity = $value;

            $this->calcTotal();
        }
    }

    public function __construct(
        public EventPlan $plan,
        int $quantity = 0,
        mixed $price = 0,
        public array $attends = [],
        public array $attendEntities = []
    ) {
        $this->price = $price;
        $this->quantity = $quantity;
    }

    public function calcTotal(): static
    {
        $this->total = $this->price->multipliedBy($this->quantity);

        return $this;
    }
}
