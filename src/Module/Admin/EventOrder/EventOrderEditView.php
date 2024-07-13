<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Module\Admin\EventOrder;

use Lyrasoft\EventBooking\Module\Admin\EventOrder\Form\EditForm;
use Lyrasoft\EventBooking\Entity\EventAttend;
use Lyrasoft\EventBooking\Entity\EventOrder;
use Lyrasoft\EventBooking\Entity\EventPlan;
use Lyrasoft\EventBooking\Entity\EventStage;
use Lyrasoft\EventBooking\Repository\EventOrderRepository;
use Unicorn\View\FormAwareViewModelTrait;
use Unicorn\View\ORMAwareViewModelTrait;
use Windwalker\Core\Application\AppContext;
use Windwalker\Core\Attributes\ViewMetadata;
use Windwalker\Core\Attributes\ViewModel;
use Windwalker\Core\Html\HtmlFrame;
use Windwalker\Core\Language\TranslatorTrait;
use Windwalker\Core\View\View;
use Windwalker\Core\View\ViewModelInterface;
use Windwalker\DI\Attributes\Autowire;

/**
 * The EventOrderEditView class.
 */
#[ViewModel(
    layout: 'event-order-edit',
    js: 'event-order-edit.js'
)]
class EventOrderEditView implements ViewModelInterface
{
    use TranslatorTrait;
    use ORMAwareViewModelTrait;
    use FormAwareViewModelTrait;

    public function __construct(
        #[Autowire] protected EventOrderRepository $repository,
    ) {
    }

    /**
     * Prepare
     *
     * @param  AppContext  $app
     * @param  View        $view
     *
     * @return  mixed
     */
    public function prepare(AppContext $app, View $view): mixed
    {
        $id = $app->input('id');

        /** @var EventOrder $item */
        $item = $this->repository->mustGetItem($id);

        $stage = $this->orm->mustFindOne(EventStage::class, $item->getStageId());

        $attends = $this->orm->from(EventAttend::class)
            ->leftJoin(
                EventPlan::class,
                'plan'
            )
            ->where('event_attend.order_id', $item->getId())
            ->groupByJoins()
            ->all(EventAttend::class);

        // Bind item for injection
        $view[EventOrder::class] = $item;

        $form = $this->createForm(EditForm::class)
            ->fill(
                [
                    'item' => $this->repository->getState()->getAndForget('edit.data')
                        ?: $this->orm->extractEntity($item)
                ]
            );

        return compact('form', 'id', 'item', 'stage', 'attends');
    }

    #[ViewMetadata]
    protected function prepareMetadata(HtmlFrame $htmlFrame): void
    {
        $htmlFrame->setTitle(
            $this->trans('unicorn.title.edit', title: '訂單')
        );
    }
}
