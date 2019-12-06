<?php

namespace App\Form\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class FundingPayment
{
    /**
     * @var int|null
     *
     * @Assert\NotBlank(message="Please provide an amount.")
     * @Assert\Range(min="100", minMessage="Sorry, but the minimum is $1.")
     */
    public $amount;
}
