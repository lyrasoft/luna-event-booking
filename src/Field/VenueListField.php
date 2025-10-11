<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Field;

use Lyrasoft\EventBooking\Entity\Venue;
use Unicorn\Field\SqlListField;
use Windwalker\DOM\HTMLElement;

class VenueListField extends SqlListField
{
    protected ?string $table = Venue::class;

    /**
     * @param  HTMLElement  $input
     *
     * @return  HTMLElement
     */
    public function prepareInput(HTMLElement $input): HTMLElement
    {
        return $input;
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
