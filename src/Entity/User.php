<?php

namespace App\Entity;

use App\Domain\UserId;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Ulid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="user")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="ulid", unique=true)
     */
    #[Groups(['profile', 'me:read'])]
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
    #[Groups(['profile'])]
    private string $auth0Username;

    /**
     * @ORM\Column(name="supporter_visible", type="boolean", options={"default":true})
     */
    #[Groups(['profile'])]
    private bool $supporterVisible = true;

    /**
     * @ORM\Column(name="coins", type="integer", options={"default":0})
     */
    #[Groups(['profile'])]
    private int $coins = 0;

    /**
     * @ORM\Column(name="created_at", type="datetimetz_immutable")
     */
    #[Groups(['profile', 'me:read'])]
    private \DateTimeImmutable $createdAt;

    /**
     * @ORM\Column(name="last_patch_note_read_at", type="datetimetz_immutable", nullable=true)
     */
    private ?\DateTimeImmutable $lastPatchNoteReadAt = null;

    public function __construct(UserId $id, string $auth0Username)
    {
        $this->id = $id->getId();
        $this->auth0Username = $auth0Username;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): UserId
    {
        return new UserId($this->id);
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function getUsername(): string
    {
        return $this->getAuth0Username();
    }

    public function getAuth0Username(): string
    {
        return $this->auth0Username;
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

    #[Groups(['profile'])]
    public function isSupporter(): bool
    {
        return $this->coins >= 100;
    }

    public function getLastPatchNoteReadAt(): ?\DateTimeImmutable
    {
        return $this->lastPatchNoteReadAt;
    }

    public function setLastPatchNoteReadAt(\DateTimeImmutable $lastPatchNoteReadAt): void
    {
        $this->lastPatchNoteReadAt = $lastPatchNoteReadAt;
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
}
