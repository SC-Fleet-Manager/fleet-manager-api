<?php

namespace App\Entity;

use App\Domain\PatchNoteId;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Uid\Ulid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PatchNoteRepository")
 * @ORM\Table(name="patch_note", indexes={
 *     @ORM\Index(name="patch_note_created_at_idx", columns={"created_at"})
 * })
 */
class PatchNote
{
    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="ulid", unique=true)
     */
    private Ulid $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private string $title;

    /**
     * @ORM\Column(type="text")
     */
    private string $body;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $link;

    /**
     * @ORM\Column(type="datetimetz_immutable")
     */
    private \DateTimeImmutable $createdAt;

    public function __construct(PatchNoteId $id, string $title, string $body, ?string $link, \DateTimeInterface $createdAt)
    {
        $this->id = $id->getId();
        $this->title = $title;
        $this->body = $body;
        $this->link = $link;
        $this->createdAt = \DateTimeImmutable::createFromInterface($createdAt);
    }

    public function getId(): PatchNoteId
    {
        return new PatchNoteId($this->id);
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function setBody(string $body): self
    {
        $this->body = $body;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
