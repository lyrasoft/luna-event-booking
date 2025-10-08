<?php

declare(strict_types=1);

namespace App\Seeder;

use Lyrasoft\EventBooking\Entity\Event;
use Lyrasoft\EventBooking\Entity\EventPlan;
use Lyrasoft\EventBooking\Entity\EventStage;
use Lyrasoft\EventBooking\Entity\Venue;
use Lyrasoft\EventBooking\EventBookingPackage;
use Lyrasoft\Luna\Entity\Category;
use Unicorn\Utilities\SlugHelper;
use Windwalker\Core\Seed\Seeder;
use Windwalker\Database\DatabaseAdapter;
use Windwalker\ORM\EntityMapper;
use Windwalker\ORM\ORM;

/**
 * Event Seeder
 *
 * @var Seeder          $seeder
 * @var ORM             $orm
 * @var DatabaseAdapter $db
 */
$seeder->import(
    static function (
        EventBookingPackage $eventBooking
    ) use (
        $seeder,
        $orm,
        $db
    ) {
        $faker = $seeder->faker($eventBooking->config('fixtures.locale') ?: 'en_US');

        /** @var EntityMapper<Event> $mapper */
        $mapper = $orm->mapper(Event::class);

        /** @var EntityMapper<EventStage> $stageMapper */
        $stageMapper = $orm->mapper(EventStage::class);

        /** @var EntityMapper<EventPlan> $planMapper */
        $planMapper = $orm->mapper(EventPlan::class);

        $categoryIds = $orm->findColumn(Category::class, 'id', ['type' => 'event'])->dump();
        $venueIds = $orm->findColumn(Venue::class, 'id')->dump();

        $createImage = function (int $w = 1200, int $h = 800) use ($faker) {
            return ['url' => $faker->unsplashImage($w, $h)];
        };

        foreach (range(1, 30) as $i) {
            $currentDate = $faker->dateTimeThisYear();

            $item = $mapper->createEntity();

            $item->categoryId = (int) $faker->randomElement($categoryIds);
            $item->title = $faker->sentence(2);
            $item->alias = SlugHelper::safe($item->title);
            $item->subtitle = $faker->sentence(3);
            $item->cover = $faker->unsplashImage(1200, 800);
            $item->images = [
                $createImage(),
                $createImage(),
                $createImage(),
            ];
            $item->intro = $faker->sentence(5);
            $item->description = $faker->paragraph(3);
            $item->state = 1;

            $event = $mapper->createOne($item);

            foreach (range(1, 4) as $s) {
                $stage = $stageMapper->createEntity();

                $stage->eventId = $event->id;
                $stage->venueId = (int) $faker->randomElement($venueIds);
                $stage->title = 'Stage ' . $s;
                $stage->cover = $faker->unsplashImage(1200, 800);
                $stage->images = [
                    $createImage(),
                    $createImage(),
                    $createImage(),
                ];
                $stage->description = $faker->paragraph(3);
                $stage->quota = 20;
                $stage->alternate = 5;
                $stage->less = 5;
                $stage->state = 1;
                $stage->ordering = $s;
                $stage->startDate = $currentDate = $currentDate->modify('+2months');
                $stage->endDate = $currentDate->modify('+14days');

                $stage = $stageMapper->createOne($stage);

                // Plans
                $plan = new EventPlan();
                $plan->eventId = $event->id;
                $plan->stageId = $stage->id;
                $plan->title = 'Early Access';
                $plan->endDate = $stage->startDate->modify('-30days');
                $plan->state = 1;
                $plan->quota = 5;
                $plan->onceMax = 1;
                $plan->originPrice = 800;
                $plan->price = 500;

                $planMapper->createOne($plan);

                $plan = new EventPlan();
                $plan->eventId = $event->id;
                $plan->stageId = $stage->id;
                $plan->title = 'Basic Ticket';
                $plan->startDate = $stage->startDate->modify('-30days');
                $plan->state = 1;
                $plan->quota = $stage->quota;
                $plan->onceMax = 1;
                $plan->price = 800;

                $planMapper->createOne($plan);

                $seeder->outCounting();
            }

            $seeder->outCounting();
        }
    }
);

$seeder->clear(
    static function () use ($seeder, $orm, $db) {
        $seeder->truncate(Event::class, EventStage::class, EventPlan::class);
    }
);
