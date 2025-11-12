<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Enum;

use Windwalker\Utilities\Attributes\Enum\Color;
use Windwalker\Utilities\Contract\LanguageInterface;
use Windwalker\Utilities\Enum\EnumRichInterface;
use Windwalker\Utilities\Enum\EnumRichTrait;

enum EventOrderState: string implements EnumRichInterface
{
    use EnumRichTrait;

    #[Color('warning')]
    case UNPAID = 'unpaid';
    #[Color('primary')]
    case PENDING_APPROVAL = 'pending_approval';
    // case PAID = 'paid';

    #[Color('success')]
    case DONE = 'done';
    #[Color('secondary')]
    case CANCEL = 'cancel';
    #[Color('danger')]
    case FAIL = 'fail';

    public function trans(LanguageInterface $lang, ...$args): string
    {
        return $lang->trans('event.order.state.' . $this->name);
    }
}
