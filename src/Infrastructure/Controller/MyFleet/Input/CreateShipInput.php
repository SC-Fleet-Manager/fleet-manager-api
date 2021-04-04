<?php

namespace App\Infrastructure\Controller\MyFleet\Input;

use App\Infrastructure\Validator\CountShipsLessThan;
use App\Infrastructure\Validator\UniqueShipNameByUser;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Url;

#[CountShipsLessThan(max: 300)]
class CreateShipInput
{
    #[NotBlank]
    #[Length(min: 2, max: 32)]
    #[UniqueShipNameByUser]
    public ?string $name = null;

    #[Url(protocols: ['https'])]
    #[Regex(pattern: '~^https://((media.)?robertsspaceindustries.com|(www.)?starcitizen.tools)~')]
    public ?string $pictureUrl = null;
}
