<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Enum;

use Windwalker\Utilities\Contract\LanguageInterface;
use Windwalker\Utilities\Enum\EnumRichInterface;
use Windwalker\Utilities\Enum\EnumRichTrait;

enum OrderHistoryType: string implements EnumRichInterface
{
    use EnumRichTrait;

    case MEMBER = 'member';
    case ADMIN = 'admin';
    case SYSTEM = 'system';

    public function trans(LanguageInterface $lang, ...$args): string
    {
        return $lang->trans('event.order.history.type.' . $this->name);
    }
}
