<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Module\Admin\EventStage;

use Lyrasoft\EventBooking\Module\Admin\EventStage\Form\GridForm;
use Lyrasoft\EventBooking\Entity\Event;
use Lyrasoft\EventBooking\Entity\EventStage;
use Lyrasoft\EventBooking\Repository\EventStageRepository;
use Lyrasoft\EventBooking\Traits\EventScopeViewTrait;
use Unicorn\View\FormAwareViewModelTrait;
use Unicorn\View\ORMAwareViewModelTrait;
use Windwalker\Core\Application\AppContext;
use Windwalker\Core\Attributes\ViewMetadata;
use Windwalker\Core\Attributes\ViewModel;
use Windwalker\Core\Html\HtmlFrame;
use Windwalker\Core\Language\TranslatorTrait;
use Windwalker\Core\View\Contract\FilterAwareViewModelInterface;
use Windwalker\Core\View\Traits\FilterAwareViewModelTrait;
use Windwalker\Core\View\View;
use Windwalker\Core\View\ViewModelInterface;
use Windwalker\DI\Attributes\Autowire;

/**
 * The EventStageListView class.
 */
#[ViewModel(
    layout: [
        'default' => 'event-stage-list',
        'modal' => 'event-stage-modal',
    ],
    js: 'event-stage-list.js'
)]
class EventStageListView implements ViewModelInterface, FilterAwareViewModelInterface
{
    use TranslatorTrait;
    use FilterAwareViewModelTrait;
    use ORMAwareViewModelTrait;
    use FormAwareViewModelTrait;
    use EventScopeViewTrait;

    public function __construct(
        #[Autowire]
        protected EventStageRepository $repository,
    ) {
    }

    /**
     * Prepare view data.
     *
     * @param  AppContext  $app   The request app context.
     * @param  View        $view  The view object.
     *
     * @return  array
     */
    public function prepare(AppContext $app, View $view): array
    {
        $state = $this->repository->getState();

        $event = $this->prepareCurrentEvent($app, $view);

        // Prepare Items
        $page     = $state->rememberFromRequest('page');
        $limit    = $state->rememberFromRequest('limit') ?? 30;
        $filter   = (array) $state->rememberFromRequest('filter');
        $search   = (array) $state->rememberFromRequest('search');
        $ordering = $state->rememberFromRequest('list_ordering') ?? $this->getDefaultOrdering();

        $items = $this->repository->getListSelector()
            ->setFilters($filter)
            ->searchTextFor(
                $search['*'] ?? '',
                $this->getSearchFields()
            )
            ->where('event_stage.event_id', $event->getId())
            ->ordering($ordering)
            ->page($page)
            ->limit($limit)
            ->setDefaultItemClass(EventStage::class);

        $pagination = $items->getPagination();

        // Prepare Form
        $form = $this->createForm(GridForm::class)
            ->fill(compact('search', 'filter'));

        $showFilters = $this->isFiltered($filter);

        return compact('items', 'pagination', 'form', 'showFilters', 'ordering', 'event');
    }

    public function reorderEnabled(string $ordering): bool
    {
        return $ordering === 'event_stage.ordering ASC';
    }

    /**
     * Get default ordering.
     *
     * @return  string
     */
    public function getDefaultOrdering(): string
    {
        return 'event_stage.id DESC';
    }

    /**
     * Get search fields.
     *
     * @return  string[]
     */
    public function getSearchFields(): array
    {
        return [
            'event_stage.id',
            'event_stage.title',
            'event_stage.alias',
            'event_stage.intro',
            'event_stage.description',
        ];
    }

    #[ViewMetadata]
    protected function prepareMetadata(HtmlFrame $htmlFrame, Event $event): void
    {
        $htmlFrame->setTitle(
            $this->trans(
                'event.edit.heading',
                event: $event->getTitle(),
                title: $this->trans('unicorn.title.grid', title: '梯次')
            )
        );
    }
}
