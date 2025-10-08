<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Entity;

use AllowDynamicProperties;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\EntitySetup;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\EntityInterface;
use Windwalker\ORM\EntityTrait;
use Windwalker\ORM\Metadata\EntityMetadata;

#[Table('event_member_maps', 'event_member_map')]
#[AllowDynamicProperties]
class EventMemberMap implements EntityInterface
{
    use EntityTrait;

    #[Column('type')]
    public string $type = '';

    #[Column('member_id')]
    public int $memberId = 0;

    #[Column('target_id')]
    public int $targetId = 0;

    #[EntitySetup]
    public static function setup(EntityMetadata $metadata): void
    {
        //
    }
}
