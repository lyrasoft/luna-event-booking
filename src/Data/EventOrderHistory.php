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

    public function __construct(
        public OrderHistoryType $type,
        public ?EventOrderState $state = null,
        public string $stateText = '',
        public bool $notify = false,
        public string $message = '',
        public mixed $userId = null,
        public ?Chronos $created = null {
            set(\DateTimeInterface|string|null $value) => Chronos::tryWrap($value);
        },
        public string $userName = ''
    ) {
        $this->id = uid();
        $this->created ??= 'now';
    }
}
