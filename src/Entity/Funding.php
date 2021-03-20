<?php

namespace App\Entity;

use App\Domain\FundingId;
use Doctrine\ORM\Mapping as ORM;
use Money\Money;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Uid\Ulid;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FundingRepository")
 * @ORM\Table(name="funding", indexes={
 *     @ORM\Index(name="funding_paypal_order_id_idx", columns={"paypal_order_id"}),
 *     @ORM\Index(name="funding_created_at_idx", columns={"created_at"})
 * })
 */
class Funding
{
    public const PAYPAL = 'paypal';

    /**
     * @ORM\Id()
     * @ORM\Column(name="id", type="ulid", unique=true)
     */
    #[Groups(['supporter', 'my_backings'])]
    private Ulid $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(name="user_id")
     */
    private ?User $user;

    /**
     * @ORM\Column(name="gateway", type="string", length=15)
     */
    #[Groups(['supporter', 'my_backings'])]
    private string $gateway;

    /**
     * @ORM\Column(name="paypal_order_id", type="string", length=31, nullable=true)
     */
    #[Groups(['supporter'])]
    private ?string $paypalOrderId;

    /**
     * @ORM\Column(name="paypal_status", type="string", length=31, nullable=true)
     */
    #[Groups(['supporter', 'my_backings'])]
    private ?string $paypalStatus;

    /**
     * @ORM\Column(name="paypal_purchase", type="json", nullable=true)
     */
    private ?array $paypalPurchase;

    /**
     * TODO : use Money\Money. see https://github.com/moneyphp/money/issues/328#issuecomment-319705165.
     *
     * In cents. (x100).
     *
     * @ORM\Column(name="amount", type="decimal", precision=10, scale=2, options={"default":0})
     */
    #[Groups(['supporter', 'my_backings'])]
    private string $amount;

    /**
     * @ORM\Column(name="net_amount", type="integer", options={"default":0})
     */
    #[Groups(['supporter'])]
    private int $netAmount = 0;

    /**
     * @ORM\Column(name="currency", type="string", length=3, options={"fixed":true})
     */
    #[Groups(['supporter', 'my_backings'])]
    private string $currency;

    /**
     * @ORM\Column(name="created_at", type="datetimetz_immutable")
     */
    #[Groups(['supporter', 'my_backings'])]
    private \DateTimeInterface $createdAt;

    /**
     * @ORM\Column(name="refunded_amount", type="integer", options={"default":0})
     */
    #[Groups(['my_backings'])]
    private int $refundedAmount = 0;

    /**
     * @ORM\Column(name="refunded_net_amount", type="integer", options={"default":0})
     */
    #[Groups(['my_backings'])]
    private int $refundedNetAmount = 0;

    /**
     * @ORM\Column(name="refunded_at", type="datetimetz_immutable", nullable=true)
     */
    #[Groups(['my_backings'])]
    private ?\DateTimeInterface $refundedAt;

    public function __construct(FundingId $id, string $gateway, Money $amount, \DateTimeInterface $createdAt)
    {
        $this->id = $id->getId();
        $this->gateway = $gateway;
        $this->amount = $amount->getAmount();
        $this->currency = $amount->getCurrency()->getCode();
        $this->createdAt = \DateTimeImmutable::createFromInterface($createdAt);
    }

    public function getId(): FundingId
    {
        return new FundingId($this->id);
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

    public function getAmount(): string
    {
        return $this->amount;
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

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getRefundedAmount(): int
    {
        return $this->refundedAmount;
    }

    public function setRefundedAmount(int $refundedAmount): self
    {
        $this->refundedAmount = $refundedAmount;

        return $this;
    }

    public function getRefundedNetAmount(): int
    {
        return $this->refundedNetAmount;
    }

    public function setRefundedNetAmount(int $refundedNetAmount): self
    {
        $this->refundedNetAmount = $refundedNetAmount;

        return $this;
    }

    public function setRefundedAt(?\DateTimeInterface $refundedAt): self
    {
        $this->refundedAt = $refundedAt;

        return $this;
    }

    #[Groups(['my_backings'])]
    public function getEffectiveAmount(): int
    {
        if ($this->amount === null) {
            return 0;
        }

        return (int) bcsub($this->amount, (string) $this->refundedAmount);
    }
}
