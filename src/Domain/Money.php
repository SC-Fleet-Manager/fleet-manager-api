<?php

namespace App\Domain;

class Money
{
    /**
     * @var int
     */
    private $cents;

    public function __construct(float $cost)
    {
        $this->cents = (int) ($cost * 100);
    }

    public function getCost(): float
    {
        return $this->cents / 100.0;
    }
}
