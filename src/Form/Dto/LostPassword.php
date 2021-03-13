<?php

namespace App\Form\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class LostPassword
{
    public function __construct(
        #[Assert\Email]
        #[Assert\NotBlank(message: "Please enter your email.")]
        public ?string $email = null
    ) {
    }
}
