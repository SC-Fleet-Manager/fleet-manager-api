<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ShipNameRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="my_hangar_name_idx", columns={"my_hangar_name"}),
 *     @ORM\Index(name="ship_matrix_name_idx", columns={"ship_matrix_name"})
 * })
 */
class ShipName
{
    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     */
    private ?UuidInterface $id = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $myHangarName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $shipMatrixName;

    public function __construct(?UuidInterface $id = null, string $myHangarName = '', string $shipMatrixName = '')
    {
        $this->id = $id;
        $this->myHangarName = $myHangarName;
        $this->shipMatrixName = $shipMatrixName;
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getMyHangarName(): string
    {
        return $this->myHangarName;
    }

    public function setMyHangarName(string $myHangarName): self
    {
        $this->myHangarName = $myHangarName;

        return $this;
    }

    public function getShipMatrixName(): string
    {
        return $this->shipMatrixName;
    }

    public function setShipMatrixName(string $shipMatrixName): self
    {
        $this->shipMatrixName = $shipMatrixName;

        return $this;
    }
}
