<?php

namespace App\Form\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class OrgaPreferences
{
    public function __construct(
        #[Assert\NotNull(message: "You must choose a supporter visibility.")]
        public ?bool $supporterVisible = null
    ) {
    }
}
