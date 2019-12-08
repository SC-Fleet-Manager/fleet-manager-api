<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MonthlyCostCoverageRepository")
 * @ORM\Table(indexes={})
 */
class MonthlyCostCoverage
{
    public const DEFAULT_DATE = '0000-01-01';

    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     *
     * @Groups({"supporter"})
     */
    private ?UuidInterface $id = null;

    /**
     * @ORM\Column(type="date_immutable", unique=true)
     *
     * @Groups({"supporter"})
     */
    private \DateTimeInterface $month;

    /**
     * @ORM\Column(type="integer")
     *
     * @Groups({"supporter"})
     */
    private int $target;

    /**
     * Allow to move the rest to next month.
     *
     * @ORM\Column(type="boolean", options={"default":1})
     */
    private bool $postpone;

    public function __construct(?UuidInterface $id = null)
    {
        $this->id = $id;
        $this->month = (new \DateTimeImmutable('first day of'))->setTime(0, 0);
        $this->target = 0;
        $this->postpone = true;
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getMonth(): \DateTimeInterface
    {
        return $this->month;
    }

    public function setMonth(\DateTimeInterface $month): self
    {
        $this->month = $month;

        return $this;
    }

    public function getTarget(): int
    {
        return $this->target;
    }

    public function setTarget(int $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function isPostpone(): bool
    {
        return $this->postpone;
    }

    public function setPostpone(bool $postpone): self
    {
        $this->postpone = $postpone;

        return $this;
    }

    public function isDefault(): bool
    {
        return $this->month->format('Y-m-d') === self::DEFAULT_DATE;
    }
}
