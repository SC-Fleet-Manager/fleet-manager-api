<?php

namespace App\Form\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ProfilePreferences
{
    public function __construct(
        #[Assert\NotNull(message: "You must choose a supporter visibility.")]
        public ?bool $supporterVisible = null
    ) {
    }
}
