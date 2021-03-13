<?php

namespace App\Form\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class FundingPayment
{
    #[Assert\NotBlank(message: "Please provide an amount.")]
    #[Assert\Range(minMessage: "Sorry, but the minimum is $1.", min: 100)]
    #[Assert\Range(maxMessage: "Could you donate a little less please? The maximum available is $9,999,999.99.", max: 999999999)]
    public ?int $amount;
}
