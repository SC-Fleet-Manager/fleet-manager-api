<?php

namespace App\Domain;

use Symfony\Component\Serializer\Annotation\Groups;

class CitizenNumber
{
    /**
     * @var string
     *
     * @Groups({"profile"})
     */
    private $number;

    public function __construct(string $number)
    {
        $this->number = $number;
    }

    public function __toString()
    {
        return $this->getNumber();
    }

    public function getNumber(): string
    {
        return (string) $this->number;
    }

    public function equals(self $other): bool
    {
        return $this->number === $other->number;
    }
}
