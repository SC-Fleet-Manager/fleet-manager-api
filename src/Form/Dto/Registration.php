<?php

namespace App\Form\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class Registration
{
    /**
     * @var string|null
     *
     * @Assert\NotBlank(message="Please enter your email.")
     * @Assert\Email()
     * @App\Validator\Constraints\UniqueField(entityClass="App\Entity\User", field="email", message="This email is already taken. Please choose another.");
     */
    public $email;

    /**
     * @var string|null
     *
     * @Assert\NotBlank(message="Please choose a password.")
     * @Assert\Length(min="6", minMessage="Some extra characters and you'll have the 6 required. ;-)")
     * //Assert\NotCompromisedPassword(message="This password is too simple. Please choose another.")
     */
    public $password;

    public function __construct(?string $email, ?string $password)
    {
        $this->email = $email;
        $this->password = $password;
    }
}
