<?php

namespace App\Domain;

use Symfony\Component\Serializer\Annotation\Groups;

class SpectrumIdentification
{
    /**
     * @var string
     *
     * @Groups({"profile"})
     */
    private $sid;

    public function __construct(string $sid)
    {
        if (\strlen($sid) < 3) {
            throw new \RuntimeException('Spectrum Id (sid) must be at least 3 characters long.');
        }
        $this->sid = mb_strtolower($sid);
    }

    public function __toString()
    {
        return $this->getSid();
    }

    public function getSid(): string
    {
        return (string) $this->sid;
    }
}
