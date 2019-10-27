<?php

namespace App\Form\Dto;

use App\Validator\Constraints\UniqueField;
use Symfony\Component\Validator\Constraints as Assert;

class Registration
{
    /**
     * @var string|null
     *
     * @Assert\NotBlank(message="Please enter your email.")
     * @Assert\Email()
     * @UniqueField(entityClass="App\Entity\User", field="email", message="This email is already taken. Please choose another.");
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
}
