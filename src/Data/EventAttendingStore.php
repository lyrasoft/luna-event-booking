<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Data;

use Brick\Math\BigDecimal;
use Lyrasoft\EventBooking\Entity\Event;
use Lyrasoft\EventBooking\Entity\EventAttend;
use Lyrasoft\EventBooking\Entity\EventOrder;
use Lyrasoft\EventBooking\Entity\EventStage;
use Windwalker\Data\Collection;
use Windwalker\Data\RecordInterface;
use Windwalker\Data\RecordTrait;
use Windwalker\Data\ValueObject;

use function Windwalker\collect;

class EventAttendingStore implements RecordInterface
{
    use RecordTrait;

    public function __construct(
        public Event $event,
        public EventStage $stage,
        public array $orderData = [],
        public ?EventOrder $order = null,
        public EventOrderTotals $totals = new EventOrderTotals() {
            set(EventOrderTotals|iterable $value) => EventOrderTotals::wrap($value);
        },
        /**
         * @var array<EventAttendingPlan>
         */
        public array $attendingPlans = [],
    ) {
    }

    /**
     * @return  Collection<array>
     */
    public function getAllAttends(): Collection
    {
        $attends = collect();

        foreach ($this->attendingPlans as $attendingPlan) {
            $attends = $attends->append(...$attendingPlan->attends);
        }

        return $attends;
    }

    /**
     * @return  Collection<EventAttend>
     */
    public function getAllAttendEntities(): Collection
    {
        $attends = collect();

        foreach ($this->attendingPlans as $attendingPlan) {
            $attends = $attends->append(...$attendingPlan->attendEntities);
        }

        return $attends;
    }

    public function getTotalQuantity(): int
    {
        $qty = 0;

        foreach ($this->attendingPlans as $attendingPlan) {
            $qty += $attendingPlan->quantity;
        }

        return $qty;
    }

    public function getGrandTotal(): BigDecimal
    {
        $gt = BigDecimal::zero();

        foreach ($this->attendingPlans as $attendingPlan) {
            $gt = $gt->plus($attendingPlan->total);
        }

        return $gt;
    }
}
