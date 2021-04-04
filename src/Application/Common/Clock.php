<?php

namespace App\Application\Common;

interface Clock
{
    public function now(): \DateTimeInterface;
}
