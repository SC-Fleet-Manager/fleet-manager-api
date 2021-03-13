<?php

namespace App\Domain;

use Symfony\Component\Serializer\Annotation\Groups;
use Webmozart\Assert\Assert;

class SpectrumIdentification
{
    #[Groups(["profile"])]
    private string $sid;

    public function __construct(string $sid)
    {
        Assert::minLength($sid, 3, 'Spectrum Id (sid) must be at least 3 characters long.');
        $this->sid = mb_strtolower($sid);
    }

    public function __toString(): string
    {
        return $this->getSid();
    }

    public function getSid(): string
    {
        return $this->sid;
    }
}
