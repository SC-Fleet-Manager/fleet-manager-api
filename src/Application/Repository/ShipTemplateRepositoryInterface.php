<?php

namespace App\Application\Repository;

use App\Domain\ShipTemplateId;
use App\Domain\TemplateAuthorId;
use App\Entity\ShipTemplate;

interface ShipTemplateRepositoryInterface
{
    public function getTemplateById(ShipTemplateId $templateId): ?ShipTemplate;

    public function save(ShipTemplate $template): void;

    /**
     * @return ShipTemplate[]
     */
    public function getTemplatesOfAuthor(TemplateAuthorId $authorId): array;
}
