<?php

namespace App\Domain;

class CitizenNumber
{
    /**
     * @var string
     */
    private $number;

    public function __construct(string $number)
    {
        $this->number = $number;
    }

    public function __toString()
    {
        return $this->number;
    }
}
