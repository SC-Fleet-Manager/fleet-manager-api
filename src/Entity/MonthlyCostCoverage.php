<?php

namespace App\Entity;

use App\Domain\MonthlyCostCoverageId;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Ulid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MonthlyCostCoverageRepository")
 * @ORM\Table(name="monthly_cost_coverage")
 */
class MonthlyCostCoverage
{
    public const DEFAULT_DATE = '1970-01-01';

    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="ulid", unique=true)
     */
    #[Groups(['supporter'])]
    private Ulid $id;

    /**
     * TODO : create a specific ValueObject.
     *
     * @ORM\Column(type="date_immutable", unique=true)
     */
    #[Groups(['supporter'])]
    private \DateTimeImmutable $month;

    /**
     * Amount in cents to reach for this month.
     *
     * @ORM\Column(type="integer")
     */
    #[Groups(['supporter'])]
    private int $target;

    /**
     * Allow to move the rest to next month.
     *
     * @ORM\Column(type="boolean", options={"default":1})
     */
    private bool $postpone = true;

    public function __construct(MonthlyCostCoverageId $id, \DateTimeInterface $month)
    {
        $this->id = $id->getId();
        $this->month = \DateTimeImmutable::createFromInterface($month)->setTime(0, 0);
        $this->target = 0;
    }

    public function getId(): MonthlyCostCoverageId
    {
        return new MonthlyCostCoverageId($this->id);
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

    public function isPast(\DateTimeInterface $now): bool
    {
        return $this->month->format('Ym') < $now->format('Ym');
    }
}
