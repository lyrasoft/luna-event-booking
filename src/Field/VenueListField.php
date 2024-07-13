<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Field;

use Lyrasoft\EventBooking\Entity\Venue;
use Unicorn\Field\SqlListField;
use Windwalker\DOM\DOMElement;

class VenueListField extends SqlListField
{
    protected ?string $table = Venue::class;

    /**
     * @param  DOMElement  $input
     *
     * @return  DOMElement
     */
    public function prepareInput(DOMElement $input): DOMElement
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
