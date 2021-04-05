<?php

namespace App\Domain;

use Symfony\Component\Uid\Ulid;

abstract class EntityId
{
    public const PATTERN = '^[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}$';

    public function __construct(
        private Ulid $ulid
    ) {
    }

    public static function fromString(string $ulid): static
    {
        return new static(Ulid::fromString($ulid));
    }

    public function getId(): Ulid
    {
        return $this->ulid;
    }

    public function equals(EntityId $other): bool
    {
        return $this->ulid->equals($other->ulid);
    }

    public function __toString(): string
    {
        return $this->ulid->toRfc4122();
    }
}
