<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Enum;

use Windwalker\Utilities\Contract\LanguageInterface;
use Windwalker\Utilities\Enum\EnumTranslatableInterface;
use Windwalker\Utilities\Enum\EnumTranslatableTrait;

enum InvoiceType: string implements EnumTranslatableInterface
{
    use EnumTranslatableTrait;

    case PERSONAL = 'personal';
    case BUSINESS = 'business';

    public function trans(LanguageInterface $lang, ...$args): string
    {
        return $lang->trans('event.invoice.type.' . $this->getKey());
    }
}
