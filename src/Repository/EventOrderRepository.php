<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Repository;

use Lyrasoft\EventBooking\Entity\Event;
use Lyrasoft\EventBooking\Entity\EventOrder;
use Lyrasoft\EventBooking\Entity\EventStage;
use Lyrasoft\EventBooking\Entity\Venue;
use Unicorn\Attributes\ConfigureAction;
use Unicorn\Attributes\Repository;
use Unicorn\Repository\Actions\BatchAction;
use Unicorn\Repository\Actions\ReorderAction;
use Unicorn\Repository\Actions\SaveAction;
use Unicorn\Repository\ListRepositoryInterface;
use Unicorn\Repository\ListRepositoryTrait;
use Unicorn\Repository\ManageRepositoryInterface;
use Unicorn\Repository\ManageRepositoryTrait;
use Unicorn\Selector\ListSelector;

#[Repository(entityClass: EventOrder::class)]
class EventOrderRepository implements ManageRepositoryInterface, ListRepositoryInterface
{
    use ManageRepositoryTrait;
    use ListRepositoryTrait;

    public function getListSelector(): ListSelector
    {
        $selector = $this->createSelector();

        $selector->from(EventOrder::class);

        return $selector;
    }

    public function getFrontListSelector(): ListSelector
    {
        $selector = $this->createSelector();

        $selector->from(EventOrder::class)
            ->leftJoin(Event::class)
            ->leftJoin(EventStage::class, 'stage')
            ->leftJoin(
                Venue::class,
                'venue',
                'stage.venue_id',
                'venue.id'
            );

        return $selector;
    }

    #[ConfigureAction(SaveAction::class)]
    protected function configureSaveAction(SaveAction $action): void
    {
        //
    }

    #[ConfigureAction(ReorderAction::class)]
    protected function configureReorderAction(ReorderAction $action): void
    {
        //
    }

    #[ConfigureAction(BatchAction::class)]
    protected function configureBatchAction(BatchAction $action): void
    {
        //
    }
}
