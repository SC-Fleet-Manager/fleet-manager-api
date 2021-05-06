<?php

namespace App\Tests\Acceptance\ShipTemplate;

use App\Application\Repository\ShipTemplateRepositoryInterface;
use App\Application\ShipTemplate\CreateTemplateService;
use App\Application\ShipTemplate\Input\CreateTemplateInput;
use App\Domain\ShipTemplateId;
use App\Domain\TemplateAuthorId;
use App\Entity\CargoCapacity;
use App\Entity\Crew;
use App\Entity\Manufacturer;
use App\Entity\Price;
use App\Entity\ShipChassis;
use App\Entity\ShipRole;
use App\Entity\ShipSize;
use App\Infrastructure\Repository\ShipTemplate\InMemoryShipTemplateRepository;
use App\Tests\Acceptance\KernelTestCase;
use Money\Money;

class CreateTemplateServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_create_a_ship_template_with_logged_user_as_author(): void
    {
        $userId = TemplateAuthorId::fromString('00000000-0000-0000-0000-000000000001');
        $templateId = ShipTemplateId::fromString('00000000-0000-0000-0000-000000000010');

        /** @var CreateTemplateService $service */
        $service = static::$container->get(CreateTemplateService::class);
        $service->handle($userId, $templateId, new CreateTemplateInput(
            model: 'Aurora MR',
            pictureUrl: 'http://example.com/aurora_mr.jpg',
            chassis: new ShipChassis('Aurora'),
            manufacturer: new Manufacturer('Robert Space Industries', 'RSI'),
            size: ShipSize::small(),
            role: new ShipRole('Combat'),
            cargoCapacity: new CargoCapacity(12),
            crew: new Crew(1, 3),
            price: new Price(Money::USD(1000), Money::UEC(500000)),
        ));

        /** @var InMemoryShipTemplateRepository $templateRepository */
        $templateRepository = static::$container->get(ShipTemplateRepositoryInterface::class);
        $template = $templateRepository->getTemplateById($templateId);
        static::assertSame('Aurora MR', $template->getModel());
    }

    /**
     * @test
     */
    public function it_should_create_a_ship_template_with_lowest_infos(): void
    {
        $userId = TemplateAuthorId::fromString('00000000-0000-0000-0000-000000000001');
        $templateId = ShipTemplateId::fromString('00000000-0000-0000-0000-000000000010');

        /** @var CreateTemplateService $service */
        $service = static::$container->get(CreateTemplateService::class);
        $service->handle($userId, $templateId, new CreateTemplateInput(
            model: 'Aurora MR',
            pictureUrl: null,
            chassis: new ShipChassis('Aurora'),
            manufacturer: new Manufacturer(),
            size: ShipSize::unknown(),
            role: new ShipRole(),
            cargoCapacity: new CargoCapacity(),
            crew: new Crew(),
            price: new Price(),
        ));

        /** @var InMemoryShipTemplateRepository $templateRepository */
        $templateRepository = static::$container->get(ShipTemplateRepositoryInterface::class);
        $template = $templateRepository->getTemplateById($templateId);
        static::assertSame('Aurora MR', $template->getModel());
    }
}
