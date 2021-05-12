<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class ShipChassis
{
    /**
     * @ORM\Column(name="name", type="string", length=60)
     */
    private string $name;

    public function __construct(string $name)
    {
        Assert::lengthBetween($name, 2, 60);
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
