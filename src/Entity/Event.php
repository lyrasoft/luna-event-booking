<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Entity;

use AllowDynamicProperties;
use DateTimeInterface;
use Lyrasoft\Luna\Attributes\Author;
use Lyrasoft\Luna\Attributes\Modifier;
use Lyrasoft\Luna\Attributes\Slugify;
use Unicorn\Enum\BasicState;
use Windwalker\Core\DateTime\Chronos;
use Windwalker\Core\DateTime\ServerTimeCast;
use Windwalker\ORM\Attributes\AutoIncrement;
use Windwalker\ORM\Attributes\Cast;
use Windwalker\ORM\Attributes\CastNullable;
use Windwalker\ORM\Attributes\Column;
use Windwalker\ORM\Attributes\CreatedTime;
use Windwalker\ORM\Attributes\CurrentTime;
use Windwalker\ORM\Attributes\EntitySetup;
use Windwalker\ORM\Attributes\PK;
use Windwalker\ORM\Attributes\Table;
use Windwalker\ORM\Cast\JsonCast;
use Windwalker\ORM\EntityInterface;
use Windwalker\ORM\EntityTrait;
use Windwalker\ORM\Metadata\EntityMetadata;

#[Table('events', 'event')]
#[AllowDynamicProperties]
class Event implements EntityInterface
{
    use EntityTrait;

    #[Column('id'), PK, AutoIncrement]
    public ?int $id = null;

    #[Column('category_id')]
    public int $categoryId = 0;

    #[Column('last_stage_id')]
    public int $lastStageId = 0;

    #[Column('title')]
    public string $title = '';

    #[Column('alias')]
    #[Slugify]
    public string $alias = '';

    #[Column('subtitle')]
    public string $subtitle = '';

    #[Column('cover')]
    public string $cover = '';

    #[Column('images')]
    #[Cast(JsonCast::class)]
    public array $images = [];

    #[Column('intro')]
    public string $intro = '';

    #[Column('description')]
    public string $description = '';

    #[Column('state')]
    #[Cast('int')]
    #[Cast(BasicState::class)]
    public BasicState $state;

    #[Column('publish_up')]
    #[CastNullable(ServerTimeCast::class)]
    public ?Chronos $publishUp = null;

    #[Column('start_date')]
    #[CastNullable(ServerTimeCast::class)]
    public ?Chronos $startDate = null;

    #[Column('end_date')]
    #[CastNullable(ServerTimeCast::class)]
    public ?Chronos $endDate = null;

    #[Column('created')]
    #[CastNullable(ServerTimeCast::class)]
    #[CreatedTime]
    public ?Chronos $created = null;

    #[Column('modified')]
    #[CastNullable(ServerTimeCast::class)]
    #[CurrentTime]
    public ?Chronos $modified = null;

    #[Column('created_by')]
    #[Author]
    public int $createdBy = 0;

    #[Column('modified_by')]
    #[Modifier]
    public int $modifiedBy = 0;

    #[Column('params')]
    #[Cast(JsonCast::class)]
    public array $params = [];

    #[EntitySetup]
    public static function setup(EntityMetadata $metadata): void
    {
        //
    }
}
