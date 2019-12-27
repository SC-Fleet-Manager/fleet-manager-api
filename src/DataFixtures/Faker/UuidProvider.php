<?php

namespace App\DataFixtures\Faker;

use Faker\Provider\Base;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class UuidProvider extends Base
{
    public function createUuid(string $uuidBase, string $suffix = ''): UuidInterface
    {
        return Uuid::fromString($uuidBase.$suffix);
    }
}
