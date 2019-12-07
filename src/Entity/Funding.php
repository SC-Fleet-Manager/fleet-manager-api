<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FundingRepository")
 * @ORM\Table(indexes={
 *     @ORM\Index(name="paypal_order_id_idx", columns={"paypal_order_id"})
 * })
 */
class Funding
{
    public const PAYPAL = 'paypal';

    /**
     * @ORM\Id()
     * @ORM\Column(type="uuid", unique=true)
     *
     * @Groups({"supporter", "my_backings"})
     */
    private ?UuidInterface $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private ?User $user = null;

    /**
     * @ORM\Column(type="string", length=15)
     *
     * @Groups({"supporter", "my_backings"})
     */
    private ?string $gateway = null;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     *
     * @Groups({"supporter"})
     */
    private ?string $paypalOrderId = null;

    /**
     * @ORM\Column(type="string", length=31, nullable=true)
     *
     * @Groups({"supporter", "my_backings"})
     */
    private ?string $paypalStatus = null;

    /**
     * @var array
     *
     * @ORM\Column(type="json", nullable=true)
     */
    private ?array $paypalPurchase = null;

    /**
     * In cents. (x100).
     *
     * @ORM\Column(type="integer")
     *
     * @Groups({"supporter", "my_backings"})
     */
    private int $amount;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Groups({"my_backings"})
     */
    private ?int $netAmount = null;

    /**
     * @ORM\Column(type="string", length=3, options={"fixed":true})
     *
     * @Groups({"supporter", "my_backings"})
     */
    private string $currency;

    /**
     * @ORM\Column(type="datetimetz_immutable")
     *
     * @Groups({"supporter", "my_backings"})
     */
    private \DateTimeInterface $createdAt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Groups({"my_backings"})
     */
    private ?int $refundedAmount = null;

    /**
     * @ORM\Column(type="datetimetz_immutable", nullable=true)
     *
     * @Groups({"my_backings"})
     */
    private ?\DateTimeInterface $refundedAt = null;

    public function __construct(?UuidInterface $id = null)
    {
        $this->id = $id;
        $this->amount = 0;
        $this->currency = 'USD';
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getGateway(): ?string
    {
        return $this->gateway;
    }

    public function setGateway(?string $gateway): self
    {
        $this->gateway = $gateway;

        return $this;
    }

    public function getPaypalOrderId(): ?string
    {
        return $this->paypalOrderId;
    }

    public function setPaypalOrderId(?string $paypalOrderId): self
    {
        $this->paypalOrderId = $paypalOrderId;

        return $this;
    }

    public function getPaypalStatus(): ?string
    {
        return $this->paypalStatus;
    }

    public function setPaypalStatus(?string $paypalStatus): self
    {
        $this->paypalStatus = $paypalStatus;

        return $this;
    }

    public function getPaypalPurchase(): ?array
    {
        return $this->paypalPurchase;
    }

    public function setPaypalPurchase(?array $paypalPurchase): self
    {
        $this->paypalPurchase = $paypalPurchase;

        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getNetAmount(): ?int
    {
        return $this->netAmount;
    }

    public function setNetAmount(?int $netAmount): self
    {
        $this->netAmount = $netAmount;

        return $this;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getRefundedAmount(): ?int
    {
        return $this->refundedAmount;
    }

    public function setRefundedAmount(?int $refundedAmount): self
    {
        $this->refundedAmount = $refundedAmount;

        return $this;
    }

    public function getRefundedAt(): ?\DateTimeInterface
    {
        return $this->refundedAt;
    }

    public function setRefundedAt(?\DateTimeInterface $refundedAt): self
    {
        $this->refundedAt = $refundedAt;

        return $this;
    }

    public function getEffectiveAmount(): int
    {
        if ($this->amount === null) {
            return 0;
        }

        return $this->amount - (int) $this->refundedAmount;
    }
}
