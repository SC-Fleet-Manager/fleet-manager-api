<?php

namespace App\Infrastructure\Provider\MyFleet;

use App\Application\Provider\ListTemplatesProviderInterface;
use App\Domain\MyFleet\UserShipTemplate;
use App\Domain\ShipTemplateId;
use App\Domain\UserId;

class InMemoryListTemplatesProvider implements ListTemplatesProviderInterface
{
    private ?UserShipTemplate $templateOfUser = null;

    public function setShipTemplateOfUser(UserShipTemplate $template): void
    {
        $this->templateOfUser = $template;
    }

    public function getShipTemplateOfUser(ShipTemplateId $templateId, UserId $userId): ?UserShipTemplate
    {
        return $this->templateOfUser;
    }
}
