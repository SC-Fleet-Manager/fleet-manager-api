<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Embeddable
 */
class ShipSize
{
    public const SIZES = ['vehicle', 'snub', 'small', 'medium', 'large', 'capital'];

    /**
     * @ORM\Column(name="size", type="string", length=10, nullable=true)
     */
    private ?string $size;

    private function __construct(?string $size)
    {
        $this->size = $size;
    }

    public function getSize(): ?string
    {
        return $this->size;
    }

    public static function unknown(): self
    {
        return new self(null);
    }

    public static function __callStatic(string $name, array $arguments): self
    {
        if (!in_array($name, self::SIZES, true)) {
            throw new \RuntimeException(sprintf('static method %s does not exist.', $name));
        }

        return new self($name);
    }
}
