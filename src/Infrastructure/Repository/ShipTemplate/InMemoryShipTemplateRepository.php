<?php

namespace App\Infrastructure\Repository\ShipTemplate;

use App\Application\Repository\ShipTemplateRepositoryInterface;
use App\Domain\ShipTemplateId;
use App\Domain\TemplateAuthorId;
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

    /**
     * {@inheritDoc}
     */
    public function getTemplatesOfAuthor(TemplateAuthorId $authorId): array
    {
        $templates = array_values(array_filter($this->templates, static function (ShipTemplate $template) use ($authorId): bool {
            return $template->getAuthorId()->equals($authorId);
        }));
        usort($templates, static function (ShipTemplate $template1, ShipTemplate $template2): int {
            return $template1->getModel() <=> $template2->getModel();
        });

        return $templates;
    }
}
