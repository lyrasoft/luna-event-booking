<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Data;

use Windwalker\Data\RecordInterface;
use Windwalker\Data\RecordTrait;

use function Windwalker\uid;

class EventOrderTotal implements RecordInterface
{
    use RecordTrait;

    public string $id = '';

    public function __construct(
        public string $type = '',
        public string $title = '',
        public string $code = '',
        public float $value = 0.0,
        public bool $protect = false,
        public array $params = [],
    ) {
        $this->id = uid();
    }
}
