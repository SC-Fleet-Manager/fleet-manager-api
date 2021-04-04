<?php

namespace App\Infrastructure\Common;

use App\Application\Common\Clock;

class FakeClock implements Clock
{
    private string $now = '1970-01-01T10:00:00Z';

    public function setNow(string $now): void
    {
        $this->now = $now;
    }

    public function now(): \DateTimeInterface
    {
        return new \DateTimeImmutable($this->now);
    }
}
