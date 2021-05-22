<?php

namespace App\Application\Provider;

use App\Domain\MyFleet\UserShipTemplate;
use App\Domain\ShipTemplateId;
use App\Domain\UserId;

interface ListTemplatesProviderInterface
{
    public function getShipTemplateOfUser(ShipTemplateId $templateId, UserId $userId): ?UserShipTemplate;
}
