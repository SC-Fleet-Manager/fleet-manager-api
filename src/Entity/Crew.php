<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class Crew
{
    /**
     * @ORM\Column(name="min", type="decimal", precision=10, scale=0, nullable=true)
     */
    private ?string $min;

    /**
     * @ORM\Column(name="max", type="decimal", precision=10, scale=0, nullable=true)
     */
    private ?string $max;

    public function __construct(?int $min = null, ?int $max = null)
    {
        if ($min !== null) {
            Assert::greaterThanEq($min, 1);
        }
        if ($max !== null) {
            Assert::greaterThanEq($max, 1);
        }
        if ($min !== null && $max !== null) {
            Assert::lessThanEq($min, $max, '$min must be less than or equal to $max.');
        }
        $this->min = $min !== null ? (string) $min : null;
        $this->max = $max !== null ? (string) $max : null;
    }

    public function getMin(): ?int
    {
        return $this->min !== null ? (int) $this->min : null;
    }

    public function getMax(): ?int
    {
        return $this->max !== null ? (int) $this->max : null;
    }
}
