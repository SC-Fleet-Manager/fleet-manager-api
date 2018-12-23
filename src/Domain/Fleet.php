<?php

namespace App\Domain;

use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

class Fleet
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
     * @var iterable|Ship[]
     *
     * @Groups({"my-fleet"})
     */
    public $ships;

    /**
     * @var \DateTimeImmutable
     *
     * @Groups({"my-fleet"})
     */
    public $uploadDate;

    /**
     * @var int
     *
     * @Groups({"my-fleet"})
     */
    public $version;

    public function __construct(UuidInterface $id, Citizen $owner)
    {
        $this->id = $id;
        $this->owner = $owner;
        $this->ships = [];
    }

    public function isUploadedDateTooClose(): bool
    {
        return $this->uploadDate >= new \DateTimeImmutable('-30 minutes');
    }

    public function createRawData(): array
    {
        $res = [];
        foreach ($this->ships as $ship) {
            $res[] = $ship->rawData;
        }

        return $res;
    }
}
