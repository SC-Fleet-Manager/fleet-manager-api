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
    public ?string $myHangarNamePattern = null;

    /**
     * @Assert\NotBlank()
     */
    public ?string $shipMatrixName = null;

    /**
     * @Assert\NotBlank()
     * @Assert\Uuid(versions={4})
     */
    public ?string $providerId;

    public function __construct(?string $myHangarName = null, ?string $shipMatrixName = null, ?string $providerId = null, ?string $myHangarNamePattern = null)
    {
        $this->myHangarName = $myHangarName;
        $this->shipMatrixName = $shipMatrixName;
        $this->providerId = $providerId;
        $this->myHangarNamePattern = $myHangarNamePattern;
    }
}
