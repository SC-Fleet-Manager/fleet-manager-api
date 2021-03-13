<?php

namespace App\Domain;

use Symfony\Component\Serializer\Annotation\Groups;

class CitizenNumber
{
    public function __construct(
        #[Groups(["profile"])]
        private string $number
    ) {
    }

    public function __toString(): string
    {
        return $this->getNumber();
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function equals(self $other): bool
    {
        return $this->number === $other->number;
    }
}
