<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Entity;

use AllowDynamicProperties;
use Brick\Math\BigDecimal;
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

// phpcs:disable
// todo: remove this when phpcs supports 8.4
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
    public InvoiceType $invoiceType {
        set(InvoiceType|string $value) => $this->invoiceType = InvoiceType::wrap($value);
    }

    #[Column('invoice_data')]
    #[Cast(JsonCast::class)]
    #[Cast(InvoiceData::class)]
    public InvoiceData $invoiceData {
        set(InvoiceData|array|null $value) => $this->invoiceData = InvoiceData::wrap($value);
        get => $this->invoiceData ??= new InvoiceData();
    }

    #[Column('total')]
    public float $total = 0.0 {
        set(float|int|string|BigNumber $value) => $this->total = BigDecimal::of($value)->toFloat();
    }

    #[Column('totals')]
    #[Cast(JsonCast::class)]
    #[Cast(EventOrderTotals::class)]
    public EventOrderTotals $totals {
        set(EventOrderTotals|array|null $value) => $this->totals = EventOrderTotals::wrap($value);
        get => $this->totals ??= new EventOrderTotals();
    }

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
    public EventOrderHistories $histories {
        set(EventOrderHistories|array|null $value) => $this->histories = EventOrderHistories::wrap($value);
        get => $this->histories ??= new EventOrderHistories();
    }

    #[Column('state')]
    #[Cast(EventOrderState::class)]
    public EventOrderState $state {
        set(EventOrderState|string $value) => $this->state = EventOrderState::wrap($value);
    }

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
    public ?Chronos $expiredAt = null {
        set(\DateTimeInterface|string|null $value) => $this->expiredAt = Chronos::tryWrap($value);
    }

    #[Column('paid_at')]
    #[CastNullable(ServerTimeCast::class)]
    public ?Chronos $paidAt = null {
        set(\DateTimeInterface|string|null $value) => $this->paidAt = Chronos::tryWrap($value);
    }

    #[Column('done_at')]
    #[CastNullable(ServerTimeCast::class)]
    public ?Chronos $doneAt = null {
        set(\DateTimeInterface|string|null $value) => $this->doneAt = Chronos::tryWrap($value);
    }

    #[Column('screenshots')]
    #[Cast(JsonCast::class)]
    public array $screenshots = [];

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
