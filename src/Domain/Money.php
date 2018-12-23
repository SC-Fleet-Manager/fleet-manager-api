<?php

namespace App\Domain;

use Symfony\Component\Serializer\Annotation\Groups;

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

    /**
     * @Groups({"my-fleet"})
     */
    public function getCost(): float
    {
        return $this->cents / 100.0;
    }
}
