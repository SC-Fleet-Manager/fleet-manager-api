<?php

namespace App\Infrastructure\Controller\MyFleet\Input;

use App\Domain\ShipId;
use App\Infrastructure\Validator\UniqueShipNameByUser;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UpdateShipInput
{
    #[Ignore]
    public ?ShipId $shipId = null;

    /**
     * @OpenApi\Property(type="string", nullable=false, minLength=2, maxLength=32, example="Avenger Titan")
     */
    #[NotBlank]
    #[Length(min: 2, max: 32)]
    public ?string $name = null;

    /**
     * @OpenApi\Property(type="string", format="url", nullable=true, example="https://media.robertsspaceindustries.com/fmhdkmvhi8ify/store_small.jpg")
     */
    #[NotBlank(allowNull: true)]
    #[Regex(
        pattern: '~^https://((media.)?robertsspaceindustries.com|(www.)?starcitizen.tools)~',
        message: 'The picture URL must be from robertsspaceindustries.com or starcitizen.tools.'
    )]
    public ?string $pictureUrl = null;

    /**
     * @OpenApi\Property(type="integer", nullable=true, minimum="1", default="1")
     */
    #[NotBlank]
    public ?int $quantity = null;

    #[Callback]
    public function validateUniqueShipNameByUser(ExecutionContextInterface $context): void
    {
        $violations = $context->getValidator()->validate($this->name, new UniqueShipNameByUser(excludeShipId: $this->shipId));
        $context->getViolations()->addAll($violations);
    }
}
