<?php

namespace App\Domain;

use Symfony\Component\Serializer\Annotation\Groups;

class HandleSC
{
    /**
     * @var string
     *
     * @Groups({"profile", "orga_fleet"})
     */
    private $handle;

    public function __construct(string $handle)
    {
        $this->handle = $handle;
    }

    public function __toString()
    {
        return $this->getHandle();
    }

    public function getHandle(): string
    {
        return $this->handle;
    }
}
