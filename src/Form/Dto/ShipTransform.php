<?php

namespace App\Form\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ShipTransform
{
    /**
     * @Assert\NotBlank()
     */
    public ?string $myHangarName = null;

    /**
     * @Assert\NotBlank()
     */
    public ?string $shipMatrixName = null;

    public function __construct(?string $myHangarName = null, ?string $shipMatrixName = null)
    {
        $this->myHangarName = $myHangarName;
        $this->shipMatrixName = $shipMatrixName;
    }
}
