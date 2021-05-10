<?php

namespace App\Infrastructure\Repository\ShipTemplate;

use App\Application\Repository\ShipTemplateRepositoryInterface;
use App\Domain\ShipTemplateId;
use App\Entity\ShipTemplate;

class InMemoryShipTemplateRepository implements ShipTemplateRepositoryInterface
{
    /** @var ShipTemplate[] */
    private array $templates;

    public function getTemplateById(ShipTemplateId $templateId): ?ShipTemplate
    {
        return $this->templates[(string) $templateId] ?? null;
    }

    public function save(ShipTemplate $template): void
    {
        $this->templates[(string) $template->getId()] = $template;
    }
}
