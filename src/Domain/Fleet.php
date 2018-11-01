<?php

namespace App\Domain;

use Ramsey\Uuid\UuidInterface;

class Fleet
{
    /**
     * @var UuidInterface
     */
    public $id;

    /**
     * @var Citizen
     */
    public $owner;

    /**
     * @var iterable|Ship[]
     */
    public $ships;

    /**
     * @var \DateTimeImmutable
     */
    public $uploadDate;

    /**
     * @var int
     */
    public $version;

    public function __construct(UuidInterface $id, Citizen $owner)
    {
        $this->id = $id;
        $this->owner = $owner;
        $this->ships = [];
    }
}
