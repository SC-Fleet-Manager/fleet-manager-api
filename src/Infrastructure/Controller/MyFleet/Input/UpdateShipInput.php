<?php

namespace App\Infrastructure\Controller\MyFleet\Input;

use App\Domain\ShipId;
use App\Infrastructure\Validator\UniqueShipModelByUser;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function Symfony\Component\String\u;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UpdateShipInput implements DenormalizableInterface
{
    #[Ignore]
    public ?ShipId $shipId = null;

    /**
     * @OpenApi\Property(type="string", nullable=false, minLength=2, maxLength=60, example="Avenger Titan")
     */
    #[NotBlank]
    #[Length(min: 2, max: 60)]
    public ?string $model = null;

    /**
     * @OpenApi\Property(type="string", format="url", nullable=true, example="https://media.robertsspaceindustries.com/fmhdkmvhi8ify/store_small.jpg")
     */
    #[NotBlank(allowNull: true)]
    #[Regex(
        pattern: '~^https://((media.)?robertsspaceindustries.com|(www.)?starcitizen.tools)/~',
        message: 'The picture URL must come from robertsspaceindustries.com or starcitizen.tools.'
    )]
    public ?string $pictureUrl = null;

    /**
     * @OpenApi\Property(type="integer", nullable=true, minimum="1", default="1")
     */
    #[NotBlank]
    public ?int $quantity = null;

    #[Callback]
    public function validateUniqueShipModelByUser(
        ExecutionContextInterface $context
    ): void {
        $violations = $context->getValidator()->validate($this->model, new UniqueShipModelByUser(excludeShipId: $this->shipId));
        $context->getViolations()->addAll($violations);
    }

    public function denormalize(DenormalizerInterface $denormalizer, $data, string $format = null, array $context = []): void
    {
        $this->model = $data['model'] ?? null;
        if ($this->model !== null) {
            $this->model = u($this->model)->trim();
        }
        $this->pictureUrl = $data['pictureUrl'] ?? null;
        $this->quantity = $data['quantity'] ?? null;
    }
}
