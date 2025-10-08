<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Module\Front\EventStage;

use Lyrasoft\EventBooking\Entity\Event;
use Lyrasoft\EventBooking\Entity\EventPlan;
use Lyrasoft\EventBooking\Entity\EventStage;
use Lyrasoft\EventBooking\Repository\EventStageRepository;
use Lyrasoft\EventBooking\Service\EventViewService;
use Lyrasoft\Luna\Module\Front\Category\CategoryViewTrait;
use Windwalker\Core\Application\AppContext;
use Windwalker\Core\Attributes\ViewMetadata;
use Windwalker\Core\Attributes\ViewModel;
use Windwalker\Core\Html\HtmlFrame;
use Windwalker\Core\Http\Browser;
use Windwalker\Core\View\View;
use Windwalker\Core\View\ViewModelInterface;
use Windwalker\DI\Attributes\Autowire;
use Windwalker\ORM\ORM;

use function Windwalker\str;

#[ViewModel(
    layout: 'event-stage-item',
    js: 'event-stage-item.js'
)]
class EventStageItemView implements ViewModelInterface
{
    use CategoryViewTrait;

    public function __construct(
        protected ORM $orm,
        protected EventViewService $eventViewService,
        #[Autowire] protected EventStageRepository $repository
    ) {
        //
    }

    /**
     * Prepare View.
     *
     * @param  AppContext  $app   The web app context.
     * @param  View        $view  The view object.
     *
     * @return  mixed
     */
    public function prepare(AppContext $app, View $view): mixed
    {
        $id = $app->input('id');
        $alias = $app->input('alias');

        /** @var EventStage $item */
        $item = $this->repository->mustGetItem($id);
        $event = $this->orm->mustFindOne(Event::class, $item->eventId);

        [, , $category] = $this->eventViewService->checkEventAndStageAvailable($event, $item);

        // Keep URL unique
        if (($item->alias !== $alias) && !$app->retrieve(Browser::class)->isRobot()) {
            return $app->getNav()->self()->alias($item->alias);
        }

        $view[$item::class] = $item;
        $view[$event::class] = $event;

        // Plans
        $plans = $this->orm->from(EventPlan::class)
            ->where('state', 1)
            ->where('stage_id', $item->id)
            ->order('start_date', 'ASC')
            ->all(EventPlan::class);

        return compact('item', 'event', 'category', 'plans');
    }

    #[ViewMetadata]
    public function prepareMetadata(HtmlFrame $htmlFrame, Event $event, EventStage $item): void
    {
        $htmlFrame->setTitle($event->title);
        $htmlFrame->setCoverImagesIfNotEmpty($event->cover);
        $htmlFrame->setDescriptionIfNotEmpty(
            (string) str($item->description)->stripHtmlTags(),
            200,
        );
    }
}
