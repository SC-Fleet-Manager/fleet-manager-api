<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\OrganizationRepository")
 */
class Organization
{
    public const PUBLIC_CHOICE_PRIVATE = 'private';
    public const PUBLIC_CHOICE_ADMIN = 'admin';
    public const PUBLIC_CHOICE_PUBLIC = 'public';
    public const PUBLIC_CHOICES = [
        self::PUBLIC_CHOICE_PRIVATE,
        self::PUBLIC_CHOICE_ADMIN,
        self::PUBLIC_CHOICE_PUBLIC,
    ];

    /**
     * @var UuidInterface
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=31, unique=true)
     * @Groups({"profile", "orga_fleet"})
     */
    private $organizationSid;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"profile", "orga_fleet"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"profile", "orga_fleet"})
     */
    private $avatarUrl;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=15, options={"default":"private"})
     */
    private $publicChoice;

    public function __construct(?UuidInterface $id = null)
    {
        $this->id = $id;
        $this->organizationSid = '';
        $this->publicChoice = self::PUBLIC_CHOICE_PRIVATE;
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getOrganizationSid(): string
    {
        return $this->organizationSid;
    }

    public function setOrganizationSid(string $sid): self
    {
        $this->organizationSid = $sid;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function setAvatarUrl(?string $avatarUrl): self
    {
        $this->avatarUrl = $avatarUrl;

        return $this;
    }

    public function getPublicChoice(): string
    {
        return $this->publicChoice;
    }

    public function setPublicChoice(string $publicChoice): self
    {
        if (in_array($publicChoice, self::PUBLIC_CHOICES, true)) {
            $this->publicChoice = $publicChoice;
        }

        return $this;
    }
}
