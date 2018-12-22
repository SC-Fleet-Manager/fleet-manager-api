<?php

namespace App\Infrastructure\Form\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class LinkAccount
{
    /**
     * @var string
     *
     * @Assert\NotBlank(message="You must enter a Starcitizen handle.")
     */
    public $handleSC;
}
