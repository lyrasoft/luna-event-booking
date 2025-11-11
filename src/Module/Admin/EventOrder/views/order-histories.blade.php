<?php

declare(strict_types=1);

namespace App\view;

/**
 * Global variables
 * --------------------------------------------------------------
 * @var $app       AppContext      Application context.
 * @var $vm        object          The view model object.
 * @var $uri       SystemUri       System Uri information.
 * @var $chronos   ChronosService  The chronos datetime service.
 * @var $nav       Navigator       Navigator object to build route.
 * @var $asset     AssetService    The Asset manage service.
 * @var $lang      LangService     The language translation service.
 */

use Lyrasoft\EventBooking\Data\EventOrderHistory;
use Lyrasoft\EventBooking\Enum\OrderHistoryType;
use Windwalker\Core\Application\AppContext;
use Windwalker\Core\Asset\AssetService;
use Windwalker\Core\DateTime\ChronosService;
use Windwalker\Core\Language\LangService;
use Windwalker\Core\Router\Navigator;
use Windwalker\Core\Router\SystemUri;
use Windwalker\Edge\Component\ComponentAttributes;

/**
 * @var EventOrderHistory   $history
 * @var ComponentAttributes $attributes
 */

$attributes->props(
    'histories',
    'order',
);

$attributes = $attributes->class('list-group');
?>

{!! $slot ?? '' !!}

<div {!! $attributes !!}>
    @foreach($histories as $history)
        <div class="list-group-item c-order-history">
            <div class="c-order-history__info d-flex text-muted mb-2">
                <div class="c-order-history__info-item mr-2">
                    <span class="fa fa-calendar"></span>
                    {{ $chronos->toLocalFormat($history->created) }}
                </div>

                @if ($history->notify)
                    <div class="c-order-history__info-item ms-2">
                    <span class="fa fa-envelope"
                        data-bs-toggle="tooltip"
                        title="@lang('event.order.history.action.notify')"></span>
                    </div>
                @endif
            </div>
            <div class="c-order-history__title">
                {{ $history->type->trans($lang) }}

                @if ($history->type !== OrderHistoryType::SYSTEM && $history->userId)
                    <a href="{{ $nav->to('user_edit', ['id' => $history->userId ?: 0]) }}">
                        {{ $history->userName }}
                    </a>
                @endif

                @if ($history->state)
                    @lang('event.order.history.action.changed.to')
                    <span class="badge bg-{{ $history->state->getColor() }}">
                    {{ $history->stateText }}
                </span>
                @endif

                @if (trim($history->message) !== '')
                    @if ($history->state)
                        @lang('event.order.history.action.and.comments')
                    @else
                        @lang('event.order.history.action.comments')
                    @endif
                @endif
            </div>

            @if (trim($history->message) !== '')
                <div class="c-order-history__message p-2 bg-light mt-2">
                    {!! html_escape($history->message, true) !!}
                </div>
            @endif
        </div>
    @endforeach
</div>
