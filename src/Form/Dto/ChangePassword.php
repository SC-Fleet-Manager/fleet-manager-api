<?php

namespace App\Form\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ChangePassword
{
    #[Assert\NotBlank(message: "You must enter your actual password.")]
    public ?string $oldPassword;

    #[Assert\NotBlank(message: "You must enter a new password.")]
    #[Assert\Length(min: 6, minMessage: "Some extra characters and you'll have the 6 required. ;-)")]
    public ?string $newPassword;
}
