<?php

namespace App\Form\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class LostPasswordCreate
{
    /**
     * @var string|null
     *
     * @Assert\NotBlank(message="Please choose a password.")
     * @Assert\Length(min="6", minMessage="Some extra characters and you'll have the 6 required. ;-)")
     * //Assert\NotCompromisedPassword(message="This password is too simple. Please choose another.")
     */
    public $password;

    public function __construct(?string $password = null)
    {
        $this->password = $password;
    }
}
