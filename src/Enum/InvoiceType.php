<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Enum;

use Windwalker\Utilities\Contract\LanguageInterface;
use Windwalker\Utilities\Enum\EnumRichInterface;
use Windwalker\Utilities\Enum\EnumRichTrait;

enum InvoiceType: string implements EnumRichInterface
{
    use EnumRichTrait;

    case PERSONAL = 'personal';
    case BUSINESS = 'business';

    public function trans(LanguageInterface $lang, ...$args): string
    {
        return $lang->trans('event.invoice.type.' . $this->name);
    }
}
