<?php

namespace App\Infrastructure\Controller\MyFleet\Input;

use OpenApi\Annotations as OpenApi;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Uuid;

class UpdateShipFromTemplateInput
{
    /**
     * @OpenApi\Property(type="string", format="uuid", nullable=false, example="00000000-0000-0000-0000-000000000001")
     */
    #[NotBlank]
    #[Uuid(strict: false)]
    public ?string $templateId = null;

    /**
     * @OpenApi\Property(type="integer", nullable=true, minimum="1", default="1", example="3")
     */
    #[LessThan(2_000_000_000)]
    public ?int $quantity = null;
}
