<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Module\Front\EventAttending;

use Lyrasoft\EventBooking\Module\Front\EventAttending\Form\EventAttendingForm;
use Lyrasoft\EventBooking\Entity\Event;
use Lyrasoft\EventBooking\Entity\EventStage;
use Lyrasoft\EventBooking\Service\EventAttendingService;
use Lyrasoft\EventBooking\Service\EventViewService;
use Lyrasoft\Luna\User\UserService;
use Unicorn\View\ORMAwareViewModelTrait;
use Windwalker\Core\Application\AppContext;
use Windwalker\Core\Attributes\ViewModel;
use Windwalker\Core\Form\FormFactory;
use Windwalker\Core\View\View;
use Windwalker\Core\View\ViewModelInterface;

#[ViewModel(
    layout: 'event-attending',
    js: 'event-attending.js'
)]
class EventAttendingView implements ViewModelInterface
{
    use ORMAwareViewModelTrait;

    /**
     * Constructor.
     */
    public function __construct(
        protected EventAttendingService $eventAttendingService,
        protected EventViewService $eventViewService,
        protected FormFactory $formFactory,
        protected UserService $userService,
    ) {
        //
    }

    /**
     * Prepare View.
     *
     * @param  AppContext  $app  The web app context.
     * @param  View        $view  The view object.
     *
     * @return  mixed
     */
    public function prepare(AppContext $app, View $view): array
    {
        $stageId = $app->input('stageId');

        $stage = $this->orm->mustFindOne(EventStage::class, $stageId);
        $event = $this->orm->mustFindOne(Event::class, $stage->getEventId());

        [, , $category] = $this->eventViewService->checkEventAndStageAvailable($event, $stage);

        $attendingStore = $this->eventAttendingService->getAttendingStore($stage);

        $user = $this->userService->getUser();

        $form = $this->formFactory->create(EventAttendingForm::class)
            ->fillTo('order', $attendingStore->getOrderData());

        if ($user->isLogin()) {
            $form['order/name']->setValue($user->getName());
            $form['order/email']->setValue($user->getEmail());
        }

        return compact(
            'event',
            'stage',
            'category',
            'attendingStore',
            'form',
        );
    }
}
