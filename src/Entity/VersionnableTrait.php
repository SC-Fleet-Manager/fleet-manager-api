<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

trait VersionnableTrait
{
    /**
     * @ORM\Version
     * @ORM\Column(name="version", type="integer", options={"default":1})
     */
    private int $version = 1;

    public function getVersion(): int
    {
        return $this->version;
    }
}
