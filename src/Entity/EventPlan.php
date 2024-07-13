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
    protected ?int $id = null;

    #[Column('event_id')]
    protected int $eventId = 0;

    #[Column('stage_id')]
    protected int $stageId = 0;

    #[Column('title')]
    protected string $title = '';

    #[Column('price')]
    protected float $price = 0.0;

    #[Column('origin_price')]
    protected ?float $originPrice = null;

    #[Column('state')]
    #[Cast('int')]
    #[Cast(BasicState::class)]
    protected BasicState $state;

    #[Column('start_date')]
    #[CastNullable(ServerTimeCast::class)]
    protected ?Chronos $startDate = null;

    #[Column('end_date')]
    #[CastNullable(ServerTimeCast::class)]
    protected ?Chronos $endDate = null;

    #[Column('require_validate')]
    #[Cast('bool', 'int')]
    protected bool $requireValidate = false;

    #[Column('quota')]
    protected int $quota = 0;

    #[Column('sold')]
    protected int $sold = 0;

    #[Column('once_max')]
    protected int $onceMax = 0;

    #[Column('created')]
    #[CastNullable(ServerTimeCast::class)]
    #[CreatedTime]
    protected ?Chronos $created = null;

    #[Column('modified')]
    #[CastNullable(ServerTimeCast::class)]
    #[CurrentTime]
    protected ?Chronos $modified = null;

    #[Column('created_by')]
    #[Author]
    protected int $createdBy = 0;

    #[Column('modified_by')]
    #[Modifier]
    protected int $modifiedBy = 0;

    #[Column('params')]
    #[Cast(JsonCast::class)]
    protected array $params = [];

    #[EntitySetup]
    public static function setup(EntityMetadata $metadata): void
    {
        //
    }

    #[BeforeCopyEvent]
    public static function beforeCopy(BeforeCopyEvent $event): void
    {
        $data = &$event->getData();

        $mapper = $event->getEntityMapper();

        while ($mapper->findOne(['title' => $data['title'], 'stage_id' => $data['stage_id']])) {
            $data['title'] = Str::increment($data['title'], '%s %d');
        }

        $data['sold'] = 0;
        $data['state'] = 0;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getEventId(): int
    {
        return $this->eventId;
    }

    public function setEventId(int $eventId): static
    {
        $this->eventId = $eventId;

        return $this;
    }

    public function getStageId(): int
    {
        return $this->stageId;
    }

    public function setStageId(int $stageId): static
    {
        $this->stageId = $stageId;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getOriginPrice(): ?float
    {
        return $this->originPrice;
    }

    public function setOriginPrice(?float $originPrice): static
    {
        $this->originPrice = $originPrice;

        return $this;
    }

    public function getState(): BasicState
    {
        return $this->state;
    }

    public function setState(int|BasicState $state): static
    {
        $this->state = BasicState::wrap($state);

        return $this;
    }

    public function getStartDate(): ?Chronos
    {
        return $this->startDate;
    }

    public function setStartDate(DateTimeInterface|string|null $startDate): static
    {
        $this->startDate = Chronos::tryWrap($startDate);

        return $this;
    }

    public function getEndDate(): ?Chronos
    {
        return $this->endDate;
    }

    public function setEndDate(DateTimeInterface|string|null $endDate): static
    {
        $this->endDate = Chronos::tryWrap($endDate);

        return $this;
    }

    public function isRequireValidate(): bool
    {
        return $this->requireValidate;
    }

    public function setRequireValidate(bool $requireValidate): static
    {
        $this->requireValidate = $requireValidate;

        return $this;
    }

    public function getQuota(): int
    {
        return $this->quota;
    }

    public function setQuota(int $quota): static
    {
        $this->quota = $quota;

        return $this;
    }

    public function getOnceMax(): int
    {
        return $this->onceMax;
    }

    public function setOnceMax(int $onceMax): static
    {
        $this->onceMax = $onceMax;

        return $this;
    }

    public function getCreated(): ?Chronos
    {
        return $this->created;
    }

    public function setCreated(DateTimeInterface|string|null $created): static
    {
        $this->created = Chronos::tryWrap($created);

        return $this;
    }

    public function getModified(): ?Chronos
    {
        return $this->modified;
    }

    public function setModified(DateTimeInterface|string|null $modified): static
    {
        $this->modified = Chronos::tryWrap($modified);

        return $this;
    }

    public function getCreatedBy(): int
    {
        return $this->createdBy;
    }

    public function setCreatedBy(int $createdBy): static
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getModifiedBy(): int
    {
        return $this->modifiedBy;
    }

    public function setModifiedBy(int $modifiedBy): static
    {
        $this->modifiedBy = $modifiedBy;

        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function setParams(array $params): static
    {
        $this->params = $params;

        return $this;
    }

    public function getSold(): int
    {
        return $this->sold;
    }

    public function setSold(int $sold): static
    {
        $this->sold = $sold;

        return $this;
    }

    public function isPublishUp(): bool
    {
        if ($this->getState()->isUnpublished()) {
            return false;
        }

        if ($this->getStartDate() && $this->getStartDate()->isFuture()) {
            return false;
        }

        if ($this->getEndDate() && $this->getEndDate()->isPast()) {
            return false;
        }

        return true;
    }
}
