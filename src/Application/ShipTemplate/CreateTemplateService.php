<?php

namespace App\Application\ShipTemplate;

use App\Application\Common\Clock;
use App\Application\Repository\ShipTemplateRepositoryInterface;
use App\Application\ShipTemplate\Input\CreateTemplateInput;
use App\Domain\ShipTemplateId;
use App\Domain\TemplateAuthorId;
use App\Entity\ShipTemplate;

class CreateTemplateService
{
    public function __construct(
        private ShipTemplateRepositoryInterface $shipTemplateRepository,
        private Clock $clock,
    ) {
    }

    public function handle(TemplateAuthorId $authorId, ShipTemplateId $templateId, CreateTemplateInput $input): void
    {
        $template = new ShipTemplate(
            $templateId,
            $authorId,
            $input->model,
            $input->pictureUrl,
            $input->chassis,
            $input->manufacturer,
            $input->size,
            $input->role,
            $input->cargoCapacity,
            $input->crew,
            $input->price,
            $this->clock->now(),
        );

        $this->shipTemplateRepository->save($template);
    }
}
