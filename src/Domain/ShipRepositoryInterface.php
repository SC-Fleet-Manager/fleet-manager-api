<?php

namespace App\Domain;

interface ShipRepositoryInterface
{
    /**
     * @return iterable|Ship[]
     */
    public function all(): iterable;

    public function distinctNames(): iterable;
}
