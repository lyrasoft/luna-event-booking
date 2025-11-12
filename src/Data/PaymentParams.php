<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Data;

use Windwalker\Data\RecordInterface;
use Windwalker\Data\RecordTrait;

class PaymentParams implements RecordInterface
{
    use RecordTrait;

    public array $input = [];

    public array $info = [];
}
