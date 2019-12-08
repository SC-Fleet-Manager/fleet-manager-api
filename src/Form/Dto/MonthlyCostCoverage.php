<?php

namespace App\Form\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class MonthlyCostCoverage
{
    /**
     * @Assert\NotBlank()
     */
    public ?\DateTimeInterface $month = null;
    /**
     * @Assert\NotBlank()
     * @Assert\Range(min="0")
     */
    public ?int $target = null;
    public bool $postpone;

    public function __construct(?\DateTimeInterface $month = null, ?int $target = null, bool $postpone = true)
    {
        $this->month = $month;
        $this->target = $target;
        $this->postpone = $postpone;
    }
}
