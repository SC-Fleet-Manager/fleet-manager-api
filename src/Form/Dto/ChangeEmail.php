<?php

namespace App\Form\Dto;

use App\Validator\Constraints\UniqueField;
use Symfony\Component\Validator\Constraints as Assert;

class ChangeEmail
{
    /**
     * @var string|null
     *
     * @Assert\NotBlank(message="Please enter your email.")
     * @Assert\Email()
     * @UniqueField(entityClass="App\Entity\User", field="email", message="This email is already taken. Please choose another.");
     */
    public $newEmail;
}
