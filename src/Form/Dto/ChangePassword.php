<?php

namespace App\Form\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ChangePassword
{
    /**
     * @var string
     *
     * @Assert\NotBlank(message="You must enter your actual password.")
     */
    public $oldPassword;

    /**
     * @var string
     *
     * @Assert\NotBlank(message="You must enter a new password.")
     * @Assert\Length(min="6", minMessage="Some extra characters and you'll have the 6 required. ;-)")
     * //Assert\NotCompromisedPassword(message="This password is too simple. Please choose another.")
     */
    public $newPassword;
}
