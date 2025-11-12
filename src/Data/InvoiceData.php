<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Data;

use Windwalker\Data\RecordInterface;
use Windwalker\Data\RecordTrait;

#[\AllowDynamicProperties]
class InvoiceData implements RecordInterface
{
    use RecordTrait;

    public function __construct(
        public string $title = '',
        public string $vat = '',
        public string $no = '',
        public string $date = '',
        public string $address = '',
        public string $mobile = '',
        public string $carrierCode = ''
    ) {
    }
}
