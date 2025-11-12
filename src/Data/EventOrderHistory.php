<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Data;

use Lyrasoft\EventBooking\Enum\EventOrderState;
use Lyrasoft\EventBooking\Enum\OrderHistoryType;
use Windwalker\Core\DateTime\Chronos;
use Windwalker\Data\RecordInterface;
use Windwalker\Data\RecordTrait;
use Windwalker\Data\ValueObject;

use function Windwalker\try_chronos;
use function Windwalker\uid;

class EventOrderHistory implements RecordInterface
{
    use RecordTrait;

    public string $id = '';

    public OrderHistoryType $type {
        set(OrderHistoryType|string $value) => OrderHistoryType::wrap($value);
    }

    public ?EventOrderState $state = null {
        set(EventOrderState|string|null $value) => EventOrderState::tryWrap($value);
    }

    public ?Chronos $created = null {
        set(\DateTimeInterface|string|null $value) => Chronos::tryWrap($value);
    }

    public function __construct(
        OrderHistoryType|string $type,
        EventOrderState|string|null $state = null,
        public string $stateText = '',
        public bool $notify = false,
        public string $message = '',
        public mixed $userId = null,
        \DateTimeInterface|string|null $created = null,
        public string $userName = ''
    ) {
        $this->created = $created ?? 'now';
        $this->state = $state;
        $this->type = $type;
        $this->id = uid();
    }
}
