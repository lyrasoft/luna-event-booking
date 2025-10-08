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

use Lyrasoft\EventBooking\Entity\EventOrder;
use Windwalker\Core\Application\AppContext;
use Windwalker\Core\Asset\AssetService;
use Windwalker\Core\DateTime\ChronosService;
use Windwalker\Core\Language\LangService;
use Windwalker\Core\Router\Navigator;
use Windwalker\Core\Router\SystemUri;
use Windwalker\Edge\Component\ComponentAttributes;

/**
 * @var $item        EventOrder
 * @var $attributes  ComponentAttributes
 */

$attributes->props(
    'item',
);
?>
<x-card class="l-order-info__col2">
    <table class="c-order-info-table">
        <tr class="">
            <th style="width: 30%">訂購者</th>
            <td>
                {{ $item->name }}
                @if ($item->nick)
                    ({{ $item->nick }})
                @endif
            </td>
        </tr>

        <tr class="">
            <th>Email</th>
            <td>{{ $item->email }}</td>
        </tr>

        <tr class="">
            <th>手機</th>
            <td>{{ $item->mobile }}</td>
        </tr>

        <tr class="">
            <th>電話</th>
            <td>{{ $item->phone }}</td>
        </tr>

        <tr class="">
            <th>地址</th>
            <td>{{ $item->address }}</td>
        </tr>
    </table>
</x-card>
