<?php

declare(strict_types=1);

namespace App\Routes;

use Lyrasoft\EventBooking\Module\Admin\EventCheckin\EventCheckinController;
use Lyrasoft\EventBooking\Module\Admin\EventCheckin\EventCheckinView;
use Windwalker\Core\Router\RouteCreator;

/** @var RouteCreator $router */

$router->group('event-checkin')
    ->register(function (RouteCreator $router) {
        $router->get('event_checkin', '/event/checkin/{token}')
            ->view(EventCheckinView::class);

        $router->post('event_checkin_submit', '/event/checkin/{token}')
            ->controller(EventCheckinController::class, 'checkin');
    });
