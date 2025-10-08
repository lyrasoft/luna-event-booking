<?php

declare(strict_types=1);

namespace App\View;

/**
 * Global variables
 * --------------------------------------------------------------
 * @var  $app       AppContext      Application context.
 * @var  $vm        \Lyrasoft\EventBooking\Module\Admin\EventPlan\EventPlanListView The view model object.
 * @var  $uri       SystemUri       System Uri information.
 * @var  $chronos   ChronosService  The chronos datetime service.
 * @var  $nav       Navigator       Navigator object to build route.
 * @var  $asset     AssetService    The Asset manage service.
 * @var  $lang      LangService     The language translation service.
 */

use Lyrasoft\EventBooking\Module\Admin\EventPlan\EventPlanListView;
use Lyrasoft\EventBooking\Entity\EventPlan;
use Unicorn\Workflow\BasicStateWorkflow;
use Windwalker\Core\Application\AppContext;
use Windwalker\Core\Asset\AssetService;
use Windwalker\Core\DateTime\ChronosService;
use Windwalker\Core\Language\LangService;
use Windwalker\Core\Router\Navigator;
use Windwalker\Core\Router\SystemUri;

use function Lyrasoft\EventBooking\numberFormat;

/**
 * @var $item EventPlan
 */

$workflow = $app->service(BasicStateWorkflow::class);
?>

@extends('event.layout.event-stage-edit-layout')

@section('toolbar-buttons')
    @include('list-toolbar')
@stop

@section('edit-content')
    <x-card>
        <form id="admin-form" action="" x-data="{ grid: $store.grid }"
            x-ref="gridForm"
            data-ordering="{{ $ordering }}"
            method="post">

            <x-filter-bar :form="$form" :open="$showFilters"></x-filter-bar>

            {{-- RESPONSIVE TABLE DESC --}}
            <div class="d-block d-lg-none mb-3">
                @lang('unicorn.grid.responsive.table.desc')
            </div>

            <div class="grid-table table-responsive-lg">
                <table class="table table-striped table-hover">
                    <thead>
                    <tr>
                        {{-- Toggle --}}
                        <th style="width: 1%">
                            <x-toggle-all></x-toggle-all>
                        </th>

                        {{-- State --}}
                        <th style="width: 5%" class="text-nowrap">
                            <x-sort field="event_plan.state">
                                @lang('unicorn.field.state')
                            </x-sort>
                        </th>

                        {{-- Title --}}
                        <th class="text-nowrap">
                            <x-sort field="event_plan.title">
                                @lang('unicorn.field.title')
                            </x-sort>
                        </th>

                        {{-- Price --}}
                        <th class="text-end">
                            <x-sort field="event_plan.price">
                                票價
                            </x-sort>
                        </th>

                        {{-- Quota / Sold --}}
                        <th class="text-end">
                            <x-sort field="event_plan.sold">
                                人數
                            </x-sort>
                        </th>

                        {{-- Sale --}}
                        <th>
                            銷售中
                        </th>

                        {{-- Start --}}
                        <th>
                            <x-sort field="event_plan.start_date">
                                開始
                            </x-sort>
                        </th>

                        {{-- End --}}
                        <th>
                            <x-sort field="event_plan.end_date">
                                結束
                            </x-sort>
                        </th>

                        {{-- Delete --}}
                        <th style="width: 1%" class="text-nowrap">
                            @lang('unicorn.field.delete')
                        </th>

                        {{-- ID --}}
                        <th style="width: 1%" class="text-nowrap text-end">
                            <x-sort field="event_plan.id">
                                @lang('unicorn.field.id')
                            </x-sort>
                        </th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($items as $i => $item)
                        <tr>
                            {{-- Checkbox --}}
                            <td>
                                <x-row-checkbox :row="$i" :id="$item->id"></x-row-checkbox>
                            </td>

                            {{-- State --}}
                            <td>
                                <x-state-dropdown color-on="text"
                                    button-style="width: 100%"
                                    use-states
                                    :workflow="$workflow"
                                    :id="$item->id"
                                    :value="$item->state"
                                ></x-state-dropdown>
                            </td>

                            {{-- Title --}}
                            <td>
                                <div>
                                    <a href="{{ $nav->to('event_plan_edit')->id($item->id) }}">
                                        {{ $item->title }}
                                    </a>

                                    @if ($item->isRequireValidate())
                                        <i class="far fa-shield-check"
                                            data-bs-toggle="tooltip"
                                            title="需要審核"
                                        ></i>
                                    @endif
                                </div>
                            </td>

                            {{-- Price --}}
                            <td class="text-end">
                                {{ $vm->priceFormat($item->price) }}
                            </td>

                            {{-- Sold / Quota --}}
                            <td class="text-end">
                                {{ numberFormat($item->sold) }}
                                /
                                {{ numberFormat($item->quota) }}
                            </td>

                            <td>
                                @if ($item->state->isUnpublished())
                                    <div class="text-danger">
                                        <i class="far fa-pause"></i>
                                        關閉
                                    </div>
                                @else
                                    @if (!$item->startDate || $item->startDate->isPast())
                                        @if (!$item->endDate || $item->endDate->isFuture())
                                            <div class="text-success">
                                                <i class="far fa-play"></i>
                                                銷售中
                                            </div>
                                        @else
                                            <div class="text-danger">
                                                <i class="far fa-stop"></i>
                                                已結束
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-warning">
                                            <i class="far fa-clock"></i>
                                            尚未開始
                                        </div>
                                    @endif
                                @endif
                            </td>

                            {{-- Start --}}
                            <td>
                                {{ $chronos->toLocalFormat($item->startDate, 'Y-m-d H:i') ?: '-' }}
                            </td>

                            {{-- End --}}
                            <td>
                                {{ $chronos->toLocalFormat($item->endDate, 'Y-m-d H:i') ?: '-' }}
                            </td>

                            {{-- Delete --}}
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    @click="grid.deleteItem('{{ $item->id }}')"
                                    data-dos
                                >
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </td>

                            {{-- ID --}}
                            <td class="text-end">
                                {{ $item->id }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="30">
                                <div class="c-grid-no-items text-center" style="padding: 125px 0;">
                                    <h3 class="text-secondary">@lang('unicorn.grid.no.items')</h3>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>

                <div>
                    <x-pagination :pagination="$pagination"></x-pagination>
                </div>
            </div>

            <div class="d-none">
                <input name="_method" type="hidden" value="PUT" />
                <x-csrf></x-csrf>
            </div>

            <x-batch-modal :form="$form" namespace="batch"></x-batch-modal>
        </form>
    </x-card>

@stop
