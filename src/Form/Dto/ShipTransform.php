<?php

namespace App\Form\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ShipTransform
{
    /**
     * @Assert\NotBlank()
     */
    public ?string $myHangarNamePattern = null;

    /**
     * @Assert\NotBlank()
     * @Assert\Uuid(versions={4})
     */
    public ?string $providerId;

    public function __construct(?string $providerId = null, ?string $myHangarNamePattern = null)
    {
        $this->providerId = $providerId;
        $this->myHangarNamePattern = $myHangarNamePattern;
    }
}
