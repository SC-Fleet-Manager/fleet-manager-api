<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="discord_idx", columns={"discord_id"})
 * })
 */
class User implements UserInterface
{
    public const PUBLIC_CHOICE_PRIVATE = 'private';
    public const PUBLIC_CHOICE_ORGANIZATION = 'orga';
    public const PUBLIC_CHOICE_PUBLIC = 'public';
    public const PUBLIC_CHOICES = [
        self::PUBLIC_CHOICE_PRIVATE,
        self::PUBLIC_CHOICE_ORGANIZATION,
        self::PUBLIC_CHOICE_PUBLIC,
    ];

    /**
     * @var UuidInterface
     *
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     * @Groups({"profile", "me:read", "orga_fleet"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     * @Groups({"profile"})
     */
    private $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=127, nullable=true)
     * @Groups({"must_not_be_exposed"})
     */
    private $password;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=false, nullable=true)
     */
    private $discordId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $discordTag;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=64, options={"fixed":true})
     * @Groups({"profile", "me:read"})
     */
    private $token;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=64, options={"fixed":true})
     * @Groups({"me:read"})
     */
    private $apiToken;

    /**
     * @var Citizen
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Citizen", cascade={"persist"})
     * @Groups({"profile", "orga_fleet"})
     */
    private $citizen;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=15, options={"default":User::PUBLIC_CHOICE_PRIVATE})
     * @Groups({"profile", "orga_fleet"})
     */
    private $publicChoice;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetimetz_immutable")
     * @Groups({"profile", "me:read"})
     */
    private $createdAt;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetimetz_immutable", nullable=true)
     */
    private $lastConnectedAt;

    public function __construct(?UuidInterface $id = null)
    {
        $this->id = $id;
        $this->publicChoice = self::PUBLIC_CHOICE_ORGANIZATION;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @Groups({"profile", "me:read"})
     */
    public function getNickname(): ?string
    {
        return $this->username;
    }

    public function getUsername(): ?string
    {
        return $this->discordId;
    }

    public function eraseCredentials(): void
    {
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getDiscordId(): ?string
    {
        return $this->discordId;
    }

    public function setDiscordId(string $discordId): self
    {
        $this->discordId = $discordId;

        return $this;
    }

    public function getDiscordTag(): ?string
    {
        return $this->discordTag;
    }

    public function setDiscordTag(?string $discordTag): self
    {
        $this->discordTag = $discordTag;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(string $apiToken): self
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    public function getCitizen(): ?Citizen
    {
        return $this->citizen;
    }

    public function setCitizen(?Citizen $citizen): self
    {
        $this->citizen = $citizen;

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

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getLastConnectedAt(): ?\DateTimeImmutable
    {
        return $this->lastConnectedAt;
    }

    public function setLastConnectedAt(?\DateTimeImmutable $lastConnectedAt): self
    {
        $this->lastConnectedAt = $lastConnectedAt;

        return $this;
    }

    public static function generateToken(): string
    {
        return bin2hex(random_bytes(32));
    }
}
