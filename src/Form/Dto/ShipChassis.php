<?php

namespace App\Form\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ShipChassis
{
    /**
     * @Assert\NotBlank()
     */
    public ?int $rsiId = null;

    /**
     * @Assert\NotBlank()
     */
    public ?string $name = null;

    public function __construct(?int $rsiId = null, ?string $name = null)
    {
        $this->rsiId = $rsiId;
        $this->name = $name;
    }
}
