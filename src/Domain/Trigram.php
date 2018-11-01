<?php

namespace App\Domain;

class Trigram
{
    /**
     * @var string
     */
    private $trigram;

    public function __construct(string $trigram)
    {
        if (\strlen($trigram) !== 3) {
            throw new \RuntimeException('Trigram must be 3 characters long.');
        }
        $this->trigram = $trigram;
    }

    public function __toString()
    {
        return $this->trigram;
    }
}
