<?php

namespace App\Form\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class LostPasswordCreate
{
    public function __construct(
        #[Assert\Length(min: 6, minMessage: "Some extra characters and you'll have the 6 required. ;-)")]
        #[Assert\NotBlank(message: "Please choose a password.")]
        public ?string $password = null
    ) {
    }
}
