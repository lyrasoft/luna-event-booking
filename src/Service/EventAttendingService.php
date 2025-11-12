<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Service;

use Lyrasoft\EventBooking\Data\EventAttendingPlan;
use Lyrasoft\EventBooking\Data\EventAttendingStore;
use Lyrasoft\EventBooking\Data\EventOrderTotal;
use Lyrasoft\EventBooking\Entity\Event;
use Lyrasoft\EventBooking\Entity\EventPlan;
use Lyrasoft\EventBooking\Entity\EventStage;
use Lyrasoft\EventBooking\Exception\InvalidPlanException;
use Windwalker\Core\Application\AppContext;
use Windwalker\Core\Form\Exception\ValidateFailException;
use Windwalker\DI\Attributes\Service;
use Windwalker\ORM\ORM;
use Windwalker\Query\Query;

/**
 * @psalm-type AttendingData = array{
 *      order: array<string, mixed>,
 *      quantity: array<int, int>,
 *      attends: array<string, array<string, mixed>>
 * }
 */
#[Service]
class EventAttendingService
{
    public function __construct(protected AppContext $app, protected ORM $orm)
    {
    }

    public static function getAttendingSessionKey(int $stageId): string
    {
        return "event.attending.$stageId";
    }

    /**
     * @param  int  $stageId
     *
     * @return  null|AttendingData
     */
    public function getAttendingDataFromSession(int $stageId): ?array
    {
        return $this->app->state->get(static::getAttendingSessionKey($stageId));
    }

    /**
     * @param  int    $stageId
     * @param  array  $data
     *
     * @return  AttendingData|null
     */
    public function rememberAttendingData(int $stageId, array $data): ?array
    {
        return $this->app->state->remember(static::getAttendingSessionKey($stageId), $data);
    }

    public function setPlansAndQuantity(int $stageId, array $map, bool $override = false): ?array
    {
        $data = (array) $this->getAttendingDataFromSession($stageId);

        $data['quantity'] ??= [];

        if ($override) {
            $data['quantity'] = [];
        }

        foreach ($map as $planId => $quantity) {
            $data['quantity'][(int) $planId] = (int) $quantity;
        }

        return $this->rememberAttendingData($stageId, $data);
    }

    public function getPlansAndQuantity(int $stageId): array
    {
        $data = (array) $this->getAttendingDataFromSession($stageId);

        return $data['quantity'] ?? [];
    }

    public function forgetAttendingData(int $stageId): void
    {
        $this->app->state->forget(static::getAttendingSessionKey($stageId));
    }

    public function validatePlan(EventStage $stage, int $planId, int $qty, bool $lock = false): EventPlan
    {
        if ($lock) {
            $plan = $this->orm->mustFindOne(EventPlan::class, $planId);
        } else {
            $plan = $this->orm->from(EventPlan::class)
                ->where('id', $planId)
                ->forUpdate()
                ->get(EventPlan::class);
        }

        /** @var EventPlan $plan */

        if ($plan->stageId !== $stage->id) {
            throw new InvalidPlanException('不正確的方案', 404);
        }

        if (!$plan->isPublishUp()) {
            throw new InvalidPlanException('不在方案銷售時間內', 403);
        }

        // Todo: handle Alternates
        if ($plan->quota < ($plan->sold + $qty)) {
            throw new InvalidPlanException('方案：' . $plan->title . ' 名額已滿', 403);
        }

        return $plan;
    }

    public function getStoreByPlansQuantity(EventStage|int $stage, array $maps, bool $lock = false): EventAttendingStore
    {
        $this->setPlansAndQuantity($stage->id, $maps, true);

        return $this->getAttendingStore($stage, $lock);
    }

    public function getAttendingStore(EventStage|int $stage, bool $lock = false): EventAttendingStore
    {
        if (is_int($stage)) {
            /** @var EventStage $stage */
            $stage = $this->orm->from(EventStage::class)
                ->where('id', $stage)
                ->tapIf(
                    $lock,
                    fn (Query $query) => $query->forUpdate()
                )
                ->get(EventStage::class);
        }

        $data = $this->getAttendingDataFromSession($stage->id);
        $event = $this->orm->mustFindOne(Event::class, $stage->eventId);

        $store = new EventAttendingStore(
            event: $event,
            stage: $stage,
            orderData: $data['order'] ?? []
        );

        $plans = &$store->attendingPlans;
        $attendGroup = $data['attends'] ?? [];

        foreach ($data['quantity'] ?? [] as $planId => $qty) {
            if (!$qty) {
                continue;
            }

            try {
                $plan = $this->validatePlan($stage, $planId, (int) $qty, $lock);
            } catch (\Exception $e) {
                $this->forgetAttendingData($stage->id);

                throw $e;
            }

            $attends = $attendGroup[$plan->id] ?? [];

            $planData = new EventAttendingPlan(
                plan: $plan,
                quantity: (int) $qty,
                price: $plan->price,
                attends: array_values($attends)
            );

            $plans[] = $planData;
        }

        // Check stage quota
        // Todo: Handle Alternate
        $quota = $stage->quota;

        if ($stage->alternate) {
            $quota += $stage->alternate;
        }

        if ($quota < ($stage->attends + $store->getTotalQuantity())) {
            throw new ValidateFailException('活動名額已滿');
        }

        $totals = $store->totals;
        $totals->set(
            'grand_total',
            new EventOrderTotal(
                title: '總計',
                value: $store->getGrandTotal()->toFloat()
            )
        );

        return $store;
    }

    public function getAttendingStorageThenForget(EventStage $stage): EventAttendingStore
    {
        $data = $this->getAttendingStore($stage);

        $this->forgetAttendingData($stage->id);

        return $data;
    }
}
