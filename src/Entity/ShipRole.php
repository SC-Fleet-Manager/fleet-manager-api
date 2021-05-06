<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class ShipRole
{
    /**
     * @ORM\Column(name="role", type="string", length=30, nullable=true)
     */
    private ?string $role;

    public function __construct(?string $role = null)
    {
        if ($role !== null) {
            Assert::lengthBetween($role, 3, 30);
        }
        $this->role = $role;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }
}
