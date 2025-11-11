<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Entity;

use AllowDynamicProperties;
use DateTimeInterface;
use Lyrasoft\EventBooking\Enum\AttendState;
use Lyrasoft\Luna\Attributes\Author;
use Lyrasoft\Luna\Attributes\Modifier;
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

// phpcs:disable
// todo: remove this when phpcs supports 8.4
#[Table('event_attends', 'event_attend')]
#[AllowDynamicProperties]
class EventAttend implements EntityInterface
{
    use EntityTrait;

    #[Column('id'), PK, AutoIncrement]
    public ?int $id = null;

    #[Column('order_id')]
    public int $orderId = 0;

    #[Column('user_id')]
    public int $userId = 0;

    #[Column('event_id')]
    public int $eventId = 0;

    #[Column('stage_id')]
    public int $stageId = 0;

    #[Column('plan_id')]
    public int $planId = 0;

    #[Column('plan_title')]
    public string $planTitle = '';

    #[Column('no')]
    public string $no = '';

    #[Column('price')]
    public float $price = 0.0;

    #[Column('name')]
    public string $name = '';

    #[Column('email')]
    public string $email = '';

    #[Column('nick')]
    public string $nick = '';

    #[Column('mobile')]
    public string $mobile = '';

    #[Column('phone')]
    public string $phone = '';

    #[Column('address')]
    public string $address = '';

    #[Column('details')]
    #[Cast(JsonCast::class)]
    public array $details = [];

    #[Column('snapshots')]
    #[Cast(JsonCast::class)]
    public array $snapshots = [];

    #[Column('state')]
    #[Cast(AttendState::class)]
    public AttendState $state {
        set(AttendState|string $value) => $this->state = AttendState::wrap($value);
    }

    #[Column('checked_in_at')]
    #[CastNullable(ServerTimeCast::class)]
    public ?Chronos $checkedInAt = null {
        set(\DateTimeInterface|string|null $value) => $this->checkedInAt = Chronos::tryWrap($value);
    }

    #[Column('alternate')]
    #[Cast('bool', 'int')]
    public bool $alternate = false;

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

    #[EntitySetup]
    public static function setup(EntityMetadata $metadata): void
    {
        //
    }
}
