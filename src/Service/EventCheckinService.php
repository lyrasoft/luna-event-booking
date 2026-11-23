<?php

declare(strict_types=1);

namespace Lyrasoft\EventBooking\Service;

use Lyrasoft\EventBooking\Entity\EventAttend;
use Lyrasoft\EventBooking\EventBookingPackage;
use Windwalker\Core\Database\ORMAwareTrait;
use Windwalker\Core\Router\Exception\RouteNotFoundException;
use Windwalker\DI\Attributes\Service;

#[Service]
class EventCheckinService
{
    use ORMAwareTrait;

    public function __construct(protected EventBookingPackage $eventBooking)
    {
    }

    public function findAttendByToken(string $token): EventAttend
    {
        $no = $this->parseToken($token);

        if ($no === null) {
            throw new RouteNotFoundException();
        }

        $attend = $this->orm->findOne(EventAttend::class, compact('no'));

        if (!$attend) {
            throw new RouteNotFoundException();
        }

        return $attend;
    }

    public function createToken(EventAttend $attend): string
    {
        $no = (string) $attend->no;

        return $no . '.' . $this->sign($no);
    }

    public function parseToken(string $token): ?string
    {
        $pos = strrpos($token, '.');

        if ($pos === false || $pos === 0) {
            return null;
        }

        $no = substr($token, 0, $pos);
        $signature = substr($token, $pos + 1);

        if (!hash_equals($this->sign($no), $signature)) {
            return null;
        }

        return $no;
    }

    protected function sign(string $no): string
    {
        return hash_hmac('sha256', 'event_checkin:' . $no, $this->getSecret());
    }

    protected function getSecret(): string
    {
        $secret = (string) $this->eventBooking->config('checkin.secret');

        if ($secret === '') {
            throw new \LogicException('Event check-in secret is not configured. Set EVENT_BOOKING_CHECKIN_SECRET.');
        }

        return $secret;
    }
}
