<?php

namespace App\Form\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ProfilePreferences
{
    /**
     * @Assert\NotNull(message="You must choose a supporter visibility.")
     */
    public ?bool $supporterVisible;

    public function __construct(?bool $supporterVisible = null)
    {
        $this->supporterVisible = $supporterVisible;
    }
}
