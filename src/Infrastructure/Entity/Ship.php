<?php

namespace App\Infrastructure\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity()
 */
class Ship
{
    /**
     * @var UuidInterface
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     */
    public $id;

    /**
     * @var array
     *
     * @ORM\Column(type="json")
     */
    public $rawData;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    public $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    public $manufacturer;

    /**
     * @var Citizen
     *
     * @ORM\ManyToOne(targetEntity="App\Infrastructure\Entity\Citizen")
     */
    public $owner;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetime")
     */
    public $pledgeDate;

    /**
     * @var float
     *
     * @ORM\Column(type="float")
     */
    public $cost;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default":false})
     */
    public $insured;

    /**
     * @var Fleet
     *
     * @ORM\ManyToOne(targetEntity="App\Infrastructure\Entity\Fleet", inversedBy="ships")
     */
    public $fleet;

    public static function fromShip(\App\Domain\Ship $ship): self
    {
        $f = new self();
        $f->id = clone $ship->id;
        $f->owner = Citizen::fromCitizen($ship->owner);
        $f->rawData = $ship->rawData;
        $f->pledgeDate = clone $ship->pledgeDate;
        $f->cost = $ship->cost->getCost();
        $f->insured = $ship->insured;
        $f->name = $ship->name;
        $f->manufacturer = $ship->manufacturer;

        return $f;
    }
}
