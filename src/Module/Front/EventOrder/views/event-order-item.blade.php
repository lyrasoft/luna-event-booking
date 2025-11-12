<?php

declare(strict_types=1);

namespace App\View;

/**
 * Global variables
 * --------------------------------------------------------------
 * @var  $app       AppContext      Application context.
 * @var  $vm        \Lyrasoft\EventBooking\Module\Front\EventOrder\EventOrderItemView  The view model object.
 * @var  $uri       SystemUri       System Uri information.
 * @var  $chronos   ChronosService  The chronos datetime service.
 * @var  $nav       Navigator       Navigator object to build route.
 * @var  $asset     AssetService    The Asset manage service.
 * @var  $lang      LangService     The language translation service.
 */

use Lyrasoft\EventBooking\Module\Front\EventOrder\EventOrderItemView;
use Lyrasoft\EventBooking\Data\EventOrderHistory;
use Lyrasoft\EventBooking\Data\EventOrderTotal;
use Lyrasoft\EventBooking\Entity\Event;
use Lyrasoft\EventBooking\Entity\EventOrder;
use Lyrasoft\EventBooking\Entity\EventStage;
use Lyrasoft\EventBooking\Service\EventPaymentService;
use Lyrasoft\EventBooking\Service\PriceFormatter;
use Windwalker\Core\Application\AppContext;
use Windwalker\Core\Asset\AssetService;
use Windwalker\Core\DateTime\ChronosService;
use Windwalker\Core\Language\LangService;
use Windwalker\Core\Router\Navigator;
use Windwalker\Core\Router\SystemUri;
use Windwalker\Form\Form;

/**
 * @var $form       Form
 * @var $item       EventOrder
 * @var $event      Event
 * @var $eventStage EventStage
 * @var $total      EventOrderTotal
 * @var $history    EventOrderHistory
 */

$priceFormatter = $app->retrieve(PriceFormatter::class);

$snapshots = $item->snapshots;

$event = $vm->tryEntity(Event::class, $snapshots['event']);
$stage = $vm->tryEntity(EventStage::class, $snapshots['stage']);

$histories = $item->histories;
?>

@extends('global.body')

@section('content')
    <div class="container my-4">

        <form id="order-form" action="" method="post">
            <div class="d-flex flex-column gap-4">
                {{-- Order Info --}}
                <div class="row l-order-info">
                    {{-- Col 1 --}}
                    <div class="col-lg-4">
                        <x-event.order-info.col1 :item="$item" :event="$event"
                            :stage="$stage"></x-event.order-info.col1>
                    </div>

                    {{-- Col 2 --}}
                    <div class="col-lg-4">
                        <x-event.order-info.col2 :item="$item"></x-event.order-info.col2>
                    </div>

                    {{-- Col 3 --}}
                    <div class="col-lg-4">
                        <x-event.order-info.col3 :item="$item"></x-event.order-info.col3>
                    </div>
                </div>

                @if ($paymentInfo)
                    <x-card header="付款資訊">
                        {!! $paymentInfo !!}
                    </x-card>
                @endif

                {{-- Atendees --}}
                <div class="card">
                    <h4 class="card-header">
                        參與人員
                    </h4>

                    <x-event.order-info.attends :item="$item" :attends="$attends"></x-event.order-info.attends>

                    <div class="card-body">
                        @php
                            $totals = $item->totals;
                        @endphp

                        @foreach ($totals as $total)
                            <div class="d-flex justify-content-end align-items-center">
                                <div>
                                    <strong>{{ $total->title }}</strong>
                                </div>
                                <div class="text-end fs-4 fw-bold" style="min-width: 150px">
                                    {{ $priceFormatter->format($total->value) }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Histories --}}
                <div class="">
                    <h4>訂單歷史</h4>

                    <table class="table table-striped">
                        <tbody>
                        @foreach ($histories as $history)
                            <tr>
                                <td>
                                    {{ $history->stateText }}
                                </td>
                                <td>
                                    {{ $history->type->getTitle($lang) }}
                                </td>
                                <td>
                                    {!! html_escape($history->message, true) !!}
                                </td>
                                <td>
                                    {{ $chronos->toLocalFormat($history->created, 'Y/m/d H:i') }}
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </form>

    </div>
@stop
