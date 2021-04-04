<?php

namespace App\Form\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class MonthlyCostCoverage
{
    public function __construct(
        #[Assert\NotBlank]
        public ?\DateTimeInterface $month = null,

        #[Assert\Range(min: 0)]
        #[Assert\NotBlank]
        public ?int $target = null,
        public bool $postpone = true
    ) {
    }
}
