<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="discord_idx", columns={"discord_id"}),
 *     @ORM\Index(name="username_idx", columns={"username"}),
 *     @ORM\Index(name="email_idx", columns={"email"})
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
     * @var string[]
     *
     * @ORM\Column(type="json_array")
     */
    private $roles;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=127, nullable=true)
     * @Groups({"profile"})
     */
    private $email;

    /**
     * When a new email is requested.
     *
     * @var string
     *
     * @ORM\Column(type="string", length=127, nullable=true)
     */
    private $pendingEmail;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default":false})
     * @Groups({"profile"})
     */
    private $emailConfirmed;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"profile", "public_profile", "me:read"})
     */
    private $nickname;

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
     * @ORM\Column(type="string", length=64, options={"fixed":true}, nullable=true)
     */
    private $lostPasswordToken;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetimetz_immutable", nullable=true)
     */
    private $lostPasswordRequestedAt;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=false, nullable=true)
     * @Groups({"profile"})
     */
    private $discordId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, unique=false, nullable=true)
     */
    private $pendingDiscordId;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=15, nullable=true)
     */
    private $discordTag;

    /**
     * For RSI account linking.
     *
     * @var string
     *
     * @ORM\Column(type="string", length=64, options={"fixed":true})
     * @Groups({"profile", "me:read"})
     */
    private $token;

    /**
     * For discussing with FM Webextension.
     *
     * @var string
     *
     * @ORM\Column(type="string", length=64, options={"fixed":true})
     * @Groups({"me:read"})
     */
    private $apiToken;

    /**
     * For registration and change email.
     *
     * @var string
     *
     * @ORM\Column(type="string", length=64, options={"fixed":true}, nullable=true)
     */
    private $registrationConfirmationToken;

    /**
     * @var Citizen
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Citizen", cascade={"persist"})
     * @ORM\JoinColumn(onDelete="SET NULL")
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
     * @var int
     *
     * @ORM\Column(type="integer", options={"default":0})
     * @Groups({"profile"})
     */
    private $coins;

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
     * @Groups({"profile", "me:read"})
     */
    private $updatedAt;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetimetz_immutable", nullable=true)
     */
    private $lastConnectedAt;

    public function __construct(?UuidInterface $id = null)
    {
        $this->id = $id;
        $this->roles = ['ROLE_USER'];
        $this->emailConfirmed = false;
        $this->publicChoice = self::PUBLIC_CHOICE_ORGANIZATION;
        $this->coins = 0;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;
        if (empty($this->roles)) {
            $this->roles[] = 'ROLE_USER';
        }

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
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

    public function getLostPasswordToken(): ?string
    {
        return $this->lostPasswordToken;
    }

    public function setLostPasswordToken(?string $lostPasswordToken): self
    {
        $this->lostPasswordToken = $lostPasswordToken;

        return $this;
    }

    public function getLostPasswordRequestedAt(): ?\DateTimeImmutable
    {
        return $this->lostPasswordRequestedAt;
    }

    public function setLostPasswordRequestedAt(?\DateTimeImmutable $lostPasswordRequestedAt): self
    {
        $this->lostPasswordRequestedAt = $lostPasswordRequestedAt;

        return $this;
    }

    public function canBeLostPasswordRequested(): bool
    {
        return $this->lostPasswordRequestedAt === null || $this->lostPasswordRequestedAt <= new \DateTimeImmutable('-1 minute');
    }

    public function isLostPasswordRequestExpired(): bool
    {
        return $this->lostPasswordRequestedAt === null || $this->lostPasswordRequestedAt <= new \DateTimeImmutable('-15 minutes');
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function getNickname(): ?string
    {
        return $this->nickname;
    }

    public function setNickname(string $nickname): self
    {
        $this->nickname = $nickname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPendingEmail(): ?string
    {
        return $this->pendingEmail;
    }

    public function setPendingEmail(?string $pendingEmail): self
    {
        $this->pendingEmail = $pendingEmail;

        return $this;
    }

    public function isEmailConfirmed(): bool
    {
        return $this->emailConfirmed;
    }

    public function setEmailConfirmed(bool $emailConfirmed): self
    {
        $this->emailConfirmed = $emailConfirmed;

        return $this;
    }

    public function eraseCredentials(): void
    {
    }

    public function getDiscordId(): ?string
    {
        return $this->discordId;
    }

    public function setDiscordId(?string $discordId): self
    {
        $this->discordId = $discordId;

        return $this;
    }

    public function getPendingDiscordId(): ?string
    {
        return $this->pendingDiscordId;
    }

    public function setPendingDiscordId(?string $pendingDiscordId): self
    {
        $this->pendingDiscordId = $pendingDiscordId;

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

    public function getRegistrationConfirmationToken(): ?string
    {
        return $this->registrationConfirmationToken;
    }

    public function setRegistrationConfirmationToken(?string $registrationConfirmationToken): self
    {
        $this->registrationConfirmationToken = $registrationConfirmationToken;

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

    public function getCoins(): int
    {
        return $this->coins;
    }

    public function setCoins(int $coins): self
    {
        $this->coins = $coins;

        return $this;
    }

    /**
     * @Groups({"profile", "public_profile", "orga_fleet"})
     */
    public function isSupporter(): bool
    {
        return $this->coins >= 100;
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

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

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
