<?php

namespace App\Domain\Service;

use App\Domain\EntityId;

interface EntityIdGeneratorInterface
{
    /**
     * @param string $class an implementation of EntityId
     */
    public function generateEntityId(string $class);
}
