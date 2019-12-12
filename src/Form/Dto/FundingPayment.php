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
     * @Assert\Range(max="999999999", maxMessage="Could you donate a little less please? The maximum available is $9,999,999.99.")
     */
    public ?int $amount = null;
}
