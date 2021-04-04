<?php

namespace App\Infrastructure\Common;

use App\Application\Common\Clock;

class SystemClock implements Clock
{
    public function now(): \DateTimeInterface
    {
        return new \DateTimeImmutable('now');
    }
}
