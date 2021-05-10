<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class CargoCapacity
{
    /**
     * @ORM\Column(name="capacity", type="integer")
     */
    private int $capacity;

    public function __construct(int $capacity = 0)
    {
        Assert::range($capacity, 0, 2_000_000_000);
        $this->capacity = $capacity;
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }
}
