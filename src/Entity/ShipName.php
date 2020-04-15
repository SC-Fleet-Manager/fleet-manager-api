<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ShipNameRepository")
 * @ORM\Table(indexes={
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
     * e.g., "^Pisces( Expedition)?$".
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $myHangarNamePattern = null;

    /**
     * SC-Galaxy Ship Id.
     *
     * @ORM\Column(type="uuid", nullable=true)
     */
    private ?UuidInterface $providerId = null;

    public function __construct(
        ?UuidInterface $id = null,
        ?UuidInterface $providerId = null,
        ?string $myHangarNamePattern = null
    ) {
        $this->id = $id;
        $this->providerId = $providerId;
        $this->myHangarNamePattern = $myHangarNamePattern;
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
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
