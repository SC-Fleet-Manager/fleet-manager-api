<?php

namespace App\Domain;

use Symfony\Component\Serializer\Annotation\Groups;

class HandleSC
{
    public function __construct(
        #[Groups(["profile", "public_profile", "orga_fleet", "orga_fleet_admin"])]
        private string $handle
    ) {
    }

    public function __toString(): string
    {
        return $this->getHandle();
    }

    public function getHandle(): string
    {
        return $this->handle;
    }
}
