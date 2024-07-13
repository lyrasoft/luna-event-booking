<?php

declare(strict_types=1);

namespace App\View;

/**
 * Global variables
 * --------------------------------------------------------------
 * @var  $app       AppContext      Application context.
 * @var  $vm        \Lyrasoft\EventBooking\Module\Front\EventAttending\EventAttendingView  The view model object.
 * @var  $uri       SystemUri       System Uri information.
 * @var  $chronos   ChronosService  The chronos datetime service.
 * @var  $nav       Navigator       Navigator object to build route.
 * @var  $asset     AssetService    The Asset manage service.
 * @var  $lang      LangService     The language translation service.
 */

use Lyrasoft\EventBooking\Module\Front\EventAttending\EventAttendingView;
use Lyrasoft\EventBooking\Data\EventAttendingStore;
use Lyrasoft\EventBooking\Entity\Event;
use Lyrasoft\EventBooking\Entity\EventPlan;
use Lyrasoft\EventBooking\Entity\EventStage;
use Lyrasoft\Luna\Entity\Category;
use Windwalker\Core\Application\AppContext;
use Windwalker\Core\Asset\AssetService;
use Windwalker\Core\DateTime\ChronosService;
use Windwalker\Core\Language\LangService;
use Windwalker\Core\Router\Navigator;
use Windwalker\Core\Router\SystemUri;
use Windwalker\Form\Form;

/**
 * @var $event                Event
 * @var $stage                EventStage
 * @var $category             Category
 * @var $plan                 EventPlan
 * @var $form                 Form
 * @var $attendingStore       EventAttendingStore
 */
?>

@extends('global.body')

@section('content')
    <div class="container l-event-attending my-4">
        <form id="attending-form" action="{{ $nav->to('event_checkout')->var('stageId', $stage->getId()) }}"
            method="post"
            uni-form-validate='{"scroll": true}'>
            <div class="mx-auto d-flex flex-column gap-4" style="max-width: 960px">
                <header class="mb-4">
                    <h2 class="text-center">填寫報名資料</h2>
                </header>

                {{-- Event Info --}}
                <x-components.event-info :event="$event" :stage="$stage" :category="$category" />

                {{-- Order Payer Form --}}
                <x-components.payer-form :form="$form" />

                {{-- Attends--}}
                <x-components.attendee-form :store="$attendingStore" />

                {{-- Totals --}}
                <x-components.plan-totals :store="$attendingStore" />

                <div class="text-center">
                    <button type="submit"
                        class="btn btn-lg btn-primary"
                        data-dos
                        style="width: 250px"
                    >
                        送出報名資料
                    </button>
                </div>
            </div>

            <div class="d-none">
                <x-csrf></x-csrf>
            </div>
        </form>
    </div>
@stop
