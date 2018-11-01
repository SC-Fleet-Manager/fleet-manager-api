<?php

namespace App\Domain;

use Ramsey\Uuid\UuidInterface;

class Ship
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
     * @var array
     */
    public $rawData;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $manufacturer;

    /**
     * @var \DateTimeImmutable
     */
    public $pledgeDate;

    /**
     * @var Money
     */
    public $cost;

    /**
     * @var bool
     */
    public $insured;

    public function __construct(UuidInterface $id, Citizen $owner)
    {
        $this->id = $id;
        $this->owner = $owner;
    }
}
