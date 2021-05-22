<?php

namespace App\Infrastructure\Provider\MyFleet;

use App\Application\Provider\ListTemplatesProviderInterface;
use App\Application\Repository\ShipTemplateRepositoryInterface;
use App\Domain\MyFleet\UserShipTemplate;
use App\Domain\ShipTemplateId;
use App\Domain\TemplateAuthorId;
use App\Domain\UserId;

class DirectCallListTemplatesProvider implements ListTemplatesProviderInterface
{
    public function __construct(
        private ShipTemplateRepositoryInterface $shipTemplateRepository
    ) {
    }

    public function getShipTemplateOfUser(ShipTemplateId $templateId, UserId $userId): ?UserShipTemplate
    {
        $template = $this->shipTemplateRepository->getTemplateById($templateId);
        if ($template !== null && $template->getAuthorId()->equals(TemplateAuthorId::fromString((string)$userId))) {
            return new UserShipTemplate(
                $template->getId(),
                $template->getModel(),
                $template->getPictureUrl(),
            );
        }

        return null;
    }
}
