<?php

namespace App\Infrastructure\Controller\MyFleet\Input;

use App\Infrastructure\Validator\CountShipsLessThan;
use App\Infrastructure\Validator\UniqueShipNameByUser;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Url;

#[CountShipsLessThan(max: 300)]
class CreateShipInput
{
    /**
     * @OpenApi\Property(type="string", nullable=false, minLength=2, maxLength=32, example="Avenger Titan")
     */
    #[NotBlank]
    #[Length(min: 2, max: 32)]
    #[UniqueShipNameByUser]
    public ?string $name = null;

    /**
     * @OpenApi\Property(type="string", format="url", nullable=false, example="https://media.robertsspaceindustries.com/fmhdkmvhi8ify/store_small.jpg")
     */
    #[Url(protocols: ['https'])]
    #[Regex(pattern: '~^https://((media.)?robertsspaceindustries.com|(www.)?starcitizen.tools)~')]
    public ?string $pictureUrl = null;
}
