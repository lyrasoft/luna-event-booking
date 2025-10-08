<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Entity;

use AllowDynamicProperties;
use Brick\Math\BigNumber;
use DateTimeInterface;
use Lyrasoft\EventBooking\Data\EventOrderHistories;
use Lyrasoft\EventBooking\Data\EventOrderTotals;
use Lyrasoft\EventBooking\Data\InvoiceData;
use Lyrasoft\EventBooking\Enum\EventOrderState;
use Lyrasoft\EventBooking\Enum\InvoiceType;
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

#[Table('event_orders', 'event_order')]
#[AllowDynamicProperties]
class EventOrder implements EntityInterface
{
    use EntityTrait;

    #[Column('id'), PK, AutoIncrement]
    public ?int $id = null;

    #[Column('user_id')]
    public int $userId = 0;

    #[Column('event_id')]
    public int $eventId = 0;

    #[Column('stage_id')]
    public int $stageId = 0;

    #[Column('no')]
    public string $no = '';

    #[Column('transaction_no')]
    public string $transactionNo = '';

    #[Column('invoice_type')]
    #[Cast(InvoiceType::class)]
    public InvoiceType $invoiceType;

    #[Column('invoice_data')]
    #[Cast(JsonCast::class)]
    #[Cast(InvoiceData::class)]
    public InvoiceData $invoiceData;

    #[Column('total')]
    public float $total = 0.0;

    #[Column('totals')]
    #[Cast(JsonCast::class)]
    #[Cast(EventOrderTotals::class)]
    public EventOrderTotals $totals;

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

    #[Column('histories')]
    #[Cast(JsonCast::class)]
    #[Cast(EventOrderHistories::class)]
    public EventOrderHistories $histories;

    #[Column('state')]
    #[Cast(EventOrderState::class)]
    public EventOrderState $state;

    #[Column('attends')]
    public int $attends = 0;

    #[Column('alternates')]
    public int $alternates = 0;

    #[Column('payment')]
    public string $payment = '';

    #[Column('payment_data')]
    #[Cast(JsonCast::class)]
    public array $paymentData = [];

    #[Column('expired_at')]
    #[CastNullable(ServerTimeCast::class)]
    public ?Chronos $expiredAt = null;

    #[Column('paid_at')]
    #[CastNullable(ServerTimeCast::class)]
    public ?Chronos $paidAt = null;

    #[Column('done_at')]
    #[CastNullable(ServerTimeCast::class)]
    public ?Chronos $doneAt = null;

    #[Column('screenshots')]
    #[Cast(JsonCast::class)]
    public array $screenshots = [];

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
