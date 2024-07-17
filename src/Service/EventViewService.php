<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Service;

use Lyrasoft\EventBooking\Entity\Event;
use Lyrasoft\EventBooking\Entity\EventStage;
use Lyrasoft\Luna\Entity\Category;
use Lyrasoft\Luna\Module\Front\Category\CategoryViewTrait;
use Windwalker\Core\Router\Exception\RouteNotFoundException;
use Windwalker\DI\Attributes\Service;
use Windwalker\ORM\ORM;

#[Service]
class EventViewService
{
    use CategoryViewTrait;

    public function __construct(protected ORM $orm)
    {
    }

    /**
     * @param  EventStage|int  $stage
     *
     * @return  array{ 0: Event, 1: EventStage, 2: ?Category }
     */
    public function checkStageAvailableById(EventStage|int $stage): array
    {
        if (is_int($stage)) {
            $stage = $this->orm->mustFindOne(EventStage::class, $stage);
        }

        $event = $this->orm->mustFindOne(Event::class, $stage->getEventId());

        [$event, $category] = $this->checkEventAvailable($event);
        $stage = $this->checkStageSelfAvailable($stage);

        return [$event, $stage, $category];
    }

    /**
     * @param  Event       $event
     * @param  EventStage  $stage
     *
     * @return  array{ 0: Event, 1: EventStage, 2: ?Category }
     */
    public function checkEventAndStageAvailable(Event $event, EventStage $stage): array
    {
        [$event, $category] = $this->checkEventAvailable($event);
        $stage = $this->checkStageSelfAvailable($stage);

        return [$event, $stage, $category];
    }

    /**
     * @param  Event  $event
     *
     * @return  array{ 0: Event, 1: ?Category }
     */
    public function checkEventAvailable(Event $event): array
    {
        if (!$event->getState()->isPublished()) {
            throw new RouteNotFoundException('Event not found.');
        }

        if ($event->getPublishUp() && $event->getPublishUp()->isFuture()) {
            throw new RouteNotFoundException('Event not started.');
        }

        if ($event->getEndDate() && $event->getEndDate()->isPast()) {
            throw new RouteNotFoundException('Event was ended.');
        }

        $category = null;

        /** @var ?Category $category */
        if ($event->getCategoryId()) {
            $category = $this->getCategory($event->getCategoryId());

            if ($category && !$category->getState()->isPublished()) {
                throw new RouteNotFoundException('Category not published.');
            }
        }

        return [$event, $category];
    }

    public function checkStageSelfAvailable(EventStage $stage): EventStage
    {
        if (!$stage->getState()->isPublished()) {
            throw new RouteNotFoundException('Event Stage not found.');
        }

        if ($stage->getPublishUp() && $stage->getPublishUp()->isFuture()) {
            throw new RouteNotFoundException('Event Stage not started.');
        }

        if ($stage->getEndDate() && $stage->getEndDate()->isPast()) {
            throw new RouteNotFoundException('Event Stage was ended.');
        }

        return $stage;
    }
}
