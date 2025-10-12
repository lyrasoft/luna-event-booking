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
use Windwalker\Core\Router\Navigator;
use Windwalker\Core\Router\RouteUri;
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
use Windwalker\ORM\Event\AfterCopyEvent;
use Windwalker\ORM\Event\BeforeCopyEvent;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\Utilities\Str;

// phpcs:disable
// todo: remove this when phpcs supports 8.4
#[Table('event_stages', 'event_stage')]
#[AllowDynamicProperties]
class EventStage implements EntityInterface
{
    use EntityTrait;

    #[Column('id'), PK, AutoIncrement]
    public ?int $id = null;

    #[Column('event_id')]
    public int $eventId = 0;

    #[Column('venue_id')]
    public int $venueId = 0;

    #[Column('title')]
    public string $title = '';

    #[Column('alias')]
    #[Slugify]
    public string $alias = '';

    #[Column('cover')]
    public string $cover = '';

    #[Column('images')]
    #[Cast(JsonCast::class)]
    public array $images = [];

    #[Column('description')]
    public string $description = '';

    #[Column('attend_url')]
    public string $attendUrl = '';

    #[Column('quota')]
    public ?int $quota = null;

    #[Column('alternate')]
    public ?int $alternate = null;

    #[Column('less')]
    public ?int $less = null;

    #[Column('attends')]
    public int $attends = 0;

    #[Column('state')]
    #[Cast('int')]
    #[Cast(BasicState::class)]
    public BasicState $state {
        set(BasicState|int $value) => $this->state = BasicState::wrap($value);
    }

    #[Column('ordering')]
    public int $ordering = 0;

    #[Column('publish_up')]
    #[CastNullable(ServerTimeCast::class)]
    public ?Chronos $publishUp = null {
        set(\DateTimeInterface|string|null $value) => $this->publishUp = Chronos::tryWrap($value);
    }

    #[Column('start_date')]
    #[CastNullable(ServerTimeCast::class)]
    public ?Chronos $startDate = null {
        set(\DateTimeInterface|string|null $value) => $this->startDate = Chronos::tryWrap($value);
    }

    #[Column('end_date')]
    #[CastNullable(ServerTimeCast::class)]
    public ?Chronos $endDate = null {
        set(\DateTimeInterface|string|null $value) => $this->endDate = Chronos::tryWrap($value);
    }

    #[Column('created')]
    #[CastNullable(ServerTimeCast::class)]
    #[CreatedTime]
    public ?Chronos $created = null {
        set(\DateTimeInterface|string|null $value) => $this->created = Chronos::tryWrap($value);
    }

    #[Column('modified')]
    #[CastNullable(ServerTimeCast::class)]
    #[CurrentTime]
    public ?Chronos $modified = null {
        set(\DateTimeInterface|string|null $value) => $this->modified = Chronos::tryWrap($value);
    }

    #[Column('created_by')]
    #[Author]
    public int $createdBy = 0;

    #[Column('modified_by')]
    #[Modifier]
    public int $modifiedBy = 0;

    #[Column('params')]
    #[Cast(JsonCast::class)]
    public array $params = [];

    public int $quotaAndAlternate {
        get => ($this->quota ?? 0) + ($this->alternate ?? 0);
    }

    #[EntitySetup]
    public static function setup(EntityMetadata $metadata): void
    {
        //
    }

    #[BeforeCopyEvent]
    public static function beforeCopy(BeforeCopyEvent $event): void
    {
        $data = &$event->data;

        $mapper = $event->entityMapper;

        while ($mapper->findOne(['title' => $data['title'], 'event_id' => $data['event_id']])) {
            $data['title'] = Str::increment($data['title'], '%s %d');
        }

        while ($mapper->findOne(['alias' => $data['alias'], 'event_id' => $data['event_id']])) {
            $data['alias'] = Str::increment($data['alias'], '%s-%d');
        }

        $data['state'] = 0;
        $data['attends'] = 0;
    }

    #[AfterCopyEvent]
    public static function afterCopy(AfterCopyEvent $event): void
    {
        $old = $event->oldEntity;

        /** @var static $item */
        $item = $event->entity;
        /** @var static $old */
        $orm = $event->orm;

        $orm->copy(
            EventPlan::class,
            [
                'stage_id' => $old?->id,
            ],
            [
                'stage_id' => $item->id,
            ]
        );
    }

    public function makeLink(Navigator $nav): RouteUri
    {
        return $nav->to('front::event_stage_item')->id($this->id)->alias($this->alias);
    }
}
