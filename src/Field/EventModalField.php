<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Field;

use Lyrasoft\EventBooking\Entity\Event;
use Unicorn\Field\ModalField;

class EventModalField extends ModalField
{
    protected function configure(): void
    {
        $this->route('event_list');
        $this->table(Event::class);
    }

    /**
     * @return  array
     */
    protected function getAccessors(): array
    {
        return array_merge(
            parent::getAccessors(),
            []
        );
    }
}
