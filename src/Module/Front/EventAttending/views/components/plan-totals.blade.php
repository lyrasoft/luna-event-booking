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

use Lyrasoft\EventBooking\Data\EventAttendingPlan;
use Lyrasoft\EventBooking\Data\EventAttendingStore;
use Lyrasoft\EventBooking\Data\EventOrderTotal;
use Lyrasoft\EventBooking\Service\PriceFormatter;
use Windwalker\Core\Application\AppContext;
use Windwalker\Core\Asset\AssetService;
use Windwalker\Core\DateTime\ChronosService;
use Windwalker\Core\Language\LangService;
use Windwalker\Core\Router\Navigator;
use Windwalker\Core\Router\SystemUri;
use Windwalker\Edge\Component\ComponentAttributes;

/**
 * @var $plan       EventAttendingPlan
 * @var $store      EventAttendingStore
 * @var $grandTotal EventOrderTotal
 * @var $attributes ComponentAttributes
 */

$attributes->props(
    'data'
);

$priceFormatter = $app->retrieve(PriceFormatter::class);

$grandTotal = $store->totals->get('grand_total');
?>

<div class="l-plan-totals">
    <h3 class="mb-3">費用</h3>

    <table class="table table-striped">
        <thead>
        <tr>
            <th>方案</th>
            <th class="text-end">單價</th>
            <th class="text-end">數量</th>
            <th class="text-end">小計</th>
        </tr>
        </thead>

        <tbody>
        @foreach ($store->attendingPlans as $plan)
            <tr>
                <td>
                    {{ $plan->plan->title }}
                </td>
                <td class="text-end">
                    {{ $priceFormatter->format($plan->price) }}
                </td>
                <td class="text-end">
                    {{ (int) $plan->quantity }}
                </td>
                <td class="text-end">
                    {{ $priceFormatter->format($plan->total) }}
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
        <tr>
            <td colspan="20">
                <div class="d-flex justify-content-end align-items-center fs-4">
                    <div class="text-end">
                        {{ $grandTotal->title }}
                    </div>
                    <div class="text-end" style="min-width: 150px">
                        {{ $priceFormatter->format($grandTotal->value) }}
                    </div>
                </div>
            </td>
        </tr>
        </tfoot>
    </table>
</div>
