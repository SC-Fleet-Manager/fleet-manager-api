<?php

namespace App\Domain;

use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

class Ship
{
    /**
     * @var UuidInterface
     *
     * @Groups({"my-fleet"})
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
     *
     * @Groups({"my-fleet"})
     */
    public $name;

    /**
     * @var string
     *
     * @Groups({"my-fleet"})
     */
    public $manufacturer;

    /**
     * @var \DateTimeImmutable
     *
     * @Groups({"my-fleet"})
     */
    public $pledgeDate;

    /**
     * @var Money
     *
     * @Groups({"my-fleet"})
     */
    public $cost;

    /**
     * @var bool
     *
     * @Groups({"my-fleet"})
     */
    public $insured;

    public function __construct(UuidInterface $id, Citizen $owner)
    {
        $this->id = $id;
        $this->owner = $owner;
    }
}
