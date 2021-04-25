<?php

namespace App\Entity;

use App\Domain\UserId;
use App\Domain\UserProfile;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Ulid;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="ulid", unique=true)
     */
    private Ulid $id;

    /**
     * @var string[]
     *
     * @ORM\Column(name="roles", type="json")
     */
    private array $roles = ['ROLE_USER'];

    /**
     * @ORM\Column(name="auth0_username", type="string", length=127, unique=true)
     */
    private string $auth0Username;

    /**
     * @ORM\Column(name="nickname", type="string", length=31, nullable=true)
     */
    private ?string $nickname;

    /**
     * @ORM\Column(name="supporter_visible", type="boolean", options={"default":true})
     */
    private bool $supporterVisible = true;

    /**
     * @ORM\Column(name="coins", type="integer", options={"default":0})
     */
    private int $coins = 0;

    /**
     * @ORM\Column(name="created_at", type="datetimetz_immutable")
     */
    private \DateTimeImmutable $createdAt;

    /**
     * @ORM\Column(name="last_patch_note_read_at", type="datetimetz_immutable", nullable=true)
     */
    private ?\DateTimeImmutable $lastPatchNoteReadAt = null;

    private UserProfile $profile;

    public function __construct(UserId $id, string $auth0Username, ?string $nickname, \DateTimeInterface $createdAt)
    {
        $this->id = $id->getId();
        $this->profile = new UserProfile();
        $this->auth0Username = $auth0Username;
        $this->nickname = $nickname;
        $this->createdAt = \DateTimeImmutable::createFromInterface($createdAt);
    }

    public function getId(): UserId
    {
        return new UserId($this->id);
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    public function getUsername(): string
    {
        return $this->getAuth0Username();
    }

    public function getAuth0Username(): string
    {
        return $this->auth0Username;
    }

    public function getNickname(): ?string
    {
        return $this->nickname ?? $this->getProfile()->getNickname();
    }

    public function changeNickname(?string $nickname): void
    {
        $this->nickname = $nickname;
    }

    public function isSupporterVisible(): bool
    {
        return $this->supporterVisible;
    }

    public function setSupporterVisible(bool $supporterVisible): void
    {
        $this->supporterVisible = $supporterVisible;
    }

    public function getCoins(): int
    {
        return $this->coins;
    }

    public function hasCoins(): bool
    {
        return $this->coins > 0;
    }

    public function setCoins(int $coins): self
    {
        $this->coins = $coins;

        return $this;
    }

    public function isSupporter(): bool
    {
        return $this->coins >= 100;
    }

    public function getLastPatchNoteReadAt(): ?\DateTimeInterface
    {
        return $this->lastPatchNoteReadAt;
    }

    public function setLastPatchNoteReadAt(?\DateTimeInterface $lastPatchNoteReadAt): void
    {
        $this->lastPatchNoteReadAt = $lastPatchNoteReadAt !== null
            ? \DateTimeImmutable::createFromInterface($lastPatchNoteReadAt)
            : null;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function hasReadPatchNote(PatchNote $lastPatchNote): bool
    {
        return $this->lastPatchNoteReadAt !== null && $lastPatchNote->getCreatedAt() <= $this->lastPatchNoteReadAt;
    }

    public function readPatchNote(PatchNote $patchNote): void
    {
        $this->lastPatchNoteReadAt = \DateTimeImmutable::createFromInterface($patchNote->getCreatedAt());
    }

    public function getProfile(): UserProfile
    {
        return $this->profile ?? new UserProfile();
    }

    public function provideProfile(?string $nickname = null, ?string $email = null, ?string $discordId = null): void
    {
        $this->profile = new UserProfile(
            $nickname,
            $email,
            $discordId,
        );
    }
}
