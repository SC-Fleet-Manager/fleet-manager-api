<?php

namespace App\Infrastructure\Controller\ShipTemplate\Input;

use OpenApi\Annotations as OpenApi;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CreateTemplateChassisInput
{
    /**
     * @OpenApi\Property(type="string", nullable=true, minLength=2, maxLength=60, example="Avenger")
     */
    #[NotBlank]
    #[Length(min: 2, max: 60)]
    public ?string $name = null;
}
