<?php

namespace App\Form\Dto;

use App\Entity\User;
use App\Validator\Constraints\UniqueField;
use Symfony\Component\Validator\Constraints as Assert;

class ChangeEmail
{
    #[Assert\NotBlank(message: "Please enter your email.")]
    #[Assert\Email]
    #[UniqueField(entityClass: User::class, field: "email", message: "This email is already taken. Please choose another.")]
    public ?string $newEmail;
}
