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
        $this->cents = (int) bcmul($cost, 100, 2);
    }

    #[Groups(["my-fleet"])]
    public function getCost(): float
    {
        return (float) bcdiv($this->cents, 100, 2);
    }
}
