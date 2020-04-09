<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ShipNameRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="my_hangar_name_idx", columns={"my_hangar_name"}),
 *     @ORM\Index(name="ship_matrix_name_idx", columns={"ship_matrix_name"}),
 *     @ORM\Index(name="my_hangar_name_pattern_idx", columns={"my_hangar_name_pattern"}),
 *     @ORM\Index(name="provider_id_idx", columns={"provider_id"})
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
     * @deprecated use $myHangarNamePattern instead
     *
     * @ORM\Column(type="string", length=255)
     */
    private string $myHangarName;

    /**
     * e.g., "^Pisces( Expedition)?$".
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $myHangarNamePattern = null;

    /**
     * @deprecated
     *
     * @ORM\Column(type="string", length=255)
     */
    private string $shipMatrixName;

    /**
     * SC-Galaxy Ship Id.
     *
     * @ORM\Column(type="uuid", nullable=true)
     */
    private ?UuidInterface $providerId = null;

    public function __construct(
        ?UuidInterface $id = null,
        string $myHangarName = '',
        string $shipMatrixName = '',
        ?UuidInterface $providerId = null,
        ?string $myHangarNamePattern = null
    ) {
        $this->id = $id;
        $this->myHangarName = $myHangarName;
        $this->shipMatrixName = $shipMatrixName;
        $this->providerId = $providerId;
        $this->myHangarNamePattern = $myHangarNamePattern;
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    /**
     * @deprecated
     */
    public function getMyHangarName(): string
    {
        return $this->myHangarName;
    }

    /**
     * @deprecated
     */
    public function setMyHangarName(string $myHangarName): self
    {
        $this->myHangarName = $myHangarName;

        return $this;
    }

    /**
     * @deprecated
     */
    public function getShipMatrixName(): string
    {
        return $this->shipMatrixName;
    }

    /**
     * @deprecated
     */
    public function setShipMatrixName(string $shipMatrixName): self
    {
        $this->shipMatrixName = $shipMatrixName;

        return $this;
    }

    public function getProviderId(): ?UuidInterface
    {
        return $this->providerId;
    }

    public function setProviderId(?UuidInterface $providerId): self
    {
        $this->providerId = $providerId;

        return $this;
    }

    public function getMyHangarNamePattern(): ?string
    {
        return $this->myHangarNamePattern;
    }

    public function setMyHangarNamePattern(?string $myHangarNamePattern): self
    {
        $this->myHangarNamePattern = $myHangarNamePattern;

        return $this;
    }
}
