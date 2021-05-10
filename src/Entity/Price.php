<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Money\Money;

/**
 * @ORM\Embeddable
 */
class Price
{
    /**
     * @ORM\Column(name="pledge", type="decimal", precision=12, scale=2, nullable=true)
     */
    private ?string $pledge;

    /**
     * @ORM\Column(name="ingame", type="decimal", precision=12, scale=2, nullable=true)
     */
    private ?string $ingame;

    public function __construct(?Money $pledge = null, ?Money $ingame = null)
    {
        $this->pledge = $pledge !== null ? $pledge->getAmount() : null;
        $this->ingame = $ingame !== null ? $ingame->getAmount() : null;
    }

    public function getPledge(): ?Money
    {
        return $this->pledge !== null ? Money::USD($this->pledge) : null;
    }

    public function getIngame(): ?Money
    {
        return $this->ingame !== null ? Money::UEC($this->ingame) : null;
    }
}
