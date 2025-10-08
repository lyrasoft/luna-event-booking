<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Entity;

use AllowDynamicProperties;
use DateTimeInterface;
use Lyrasoft\Luna\Attributes\Author;
use Lyrasoft\Luna\Attributes\Modifier;
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
use Windwalker\ORM\Event\BeforeCopyEvent;
use Windwalker\ORM\Metadata\EntityMetadata;
use Windwalker\Utilities\Str;

#[Table('event_plans', 'event_plan')]
#[AllowDynamicProperties]
class EventPlan implements EntityInterface
{
    use EntityTrait;

    #[Column('id'), PK, AutoIncrement]
    public ?int $id = null;

    #[Column('event_id')]
    public int $eventId = 0;

    #[Column('stage_id')]
    public int $stageId = 0;

    #[Column('title')]
    public string $title = '';

    #[Column('price')]
    public float $price = 0.0;

    #[Column('origin_price')]
    public ?float $originPrice = null;

    #[Column('state')]
    #[Cast('int')]
    #[Cast(BasicState::class)]
    public BasicState $state;

    #[Column('start_date')]
    #[CastNullable(ServerTimeCast::class)]
    public ?Chronos $startDate = null;

    #[Column('end_date')]
    #[CastNullable(ServerTimeCast::class)]
    public ?Chronos $endDate = null;

    #[Column('require_validate')]
    #[Cast('bool', 'int')]
    public bool $requireValidate = false;

    #[Column('quota')]
    public int $quota = 0;

    #[Column('sold')]
    public int $sold = 0;

    #[Column('once_max')]
    public int $onceMax = 0;

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

    #[BeforeCopyEvent]
    public static function beforeCopy(BeforeCopyEvent $event): void
    {
        $data = &$event->data;

        $mapper = $event->entityMapper;

        while ($mapper->findOne(['title' => $data['title'], 'stage_id' => $data['stage_id']])) {
            $data['title'] = Str::increment($data['title'], '%s %d');
        }

        $data['sold'] = 0;
        $data['state'] = 0;
    }

    public function isPublishUp(): bool
    {
        if ($this->state->isUnpublished()) {
            return false;
        }

        if ($this->startDate && $this->startDate->isFuture()) {
            return false;
        }

        if ($this->endDate && $this->endDate->isPast()) {
            return false;
        }

        return true;
    }
}
