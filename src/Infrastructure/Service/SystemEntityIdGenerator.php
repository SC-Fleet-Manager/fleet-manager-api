<?php

namespace App\Infrastructure\Service;

use App\Domain\EntityId;
use App\Domain\Service\EntityIdGeneratorInterface;
use Symfony\Component\Uid\Ulid;
use Webmozart\Assert\Assert;

class SystemEntityIdGenerator implements EntityIdGeneratorInterface
{
    public function generateEntityId(string $class): EntityId
    {
        Assert::isAOf($class, EntityId::class);

        return new $class(new Ulid());
    }
}
