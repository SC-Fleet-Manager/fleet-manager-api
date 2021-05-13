<?php

namespace App\Application\ShipTemplate;

use App\Application\Repository\ShipTemplateRepositoryInterface;
use App\Application\ShipTemplate\Output\CargoCapacityOutput;
use App\Application\ShipTemplate\Output\CrewOutput;
use App\Application\ShipTemplate\Output\ListTemplatesItemOutput;
use App\Application\ShipTemplate\Output\ListTemplatesOutput;
use App\Application\ShipTemplate\Output\ManufacturerOutput;
use App\Application\ShipTemplate\Output\PriceOutput;
use App\Application\ShipTemplate\Output\ShipChassisOutput;
use App\Domain\TemplateAuthorId;

class MyTemplatesService
{
    public function __construct(
        private ShipTemplateRepositoryInterface $shipTemplateRepository,
    ) {
    }

    public function handle(TemplateAuthorId $authorId): ListTemplatesOutput
    {
        $templates = $this->shipTemplateRepository->getTemplatesOfAuthor($authorId);

        $items = [];
        foreach ($templates as $template) {
            $items[] = new ListTemplatesItemOutput(
                $template->getId(),
                $template->getModel(),
                $template->getPictureUrl(),
                ShipChassisOutput::fromEntity($template->getChassis()),
                ManufacturerOutput::fromEntity($template->getManufacturer()),
                $template->getSize()->getSize(),
                $template->getRole()->getRole(),
                CargoCapacityOutput::fromEntity($template->getCargoCapacity()),
                CrewOutput::fromEntity($template->getCrew()),
                PriceOutput::fromEntity($template->getPrice()),
            );
        }

        return new ListTemplatesOutput($items);
    }
}
