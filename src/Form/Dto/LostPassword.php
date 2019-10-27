<?php

namespace App\Form\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class LostPassword
{
    /**
     * @var string|null
     *
     * @Assert\NotBlank(message="Please enter your email.")
     * @Assert\Email()
     */
    public $email;

    public function __construct(?string $email)
    {
        $this->email = $email;
    }
}
