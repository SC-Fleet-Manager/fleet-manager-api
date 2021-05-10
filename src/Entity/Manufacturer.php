<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use function Symfony\Component\String\u;
use Webmozart\Assert\Assert;

/**
 * @ORM\Embeddable
 */
class Manufacturer
{
    /**
     * @ORM\Column(name="name", type="string", length=50, nullable=true)
     */
    private ?string $name;

    /**
     * @ORM\Column(name="code", type="string", length=5, nullable=true)
     */
    private ?string $code;

    public function __construct(?string $name = null, ?string $code = null)
    {
        if ($name !== null) {
            Assert::lengthBetween($name, 3, 50);
        }
        if ($code !== null) {
            Assert::regex($code, '~^[a-zA-Z]{3,5}$~', '$code must contain only 3 to 5 letters.');
        }
        $this->name = $name;
        $this->code = $code;
        if ($this->code !== null) {
            $this->code = u($this->code)->upper();
        }
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }
}
