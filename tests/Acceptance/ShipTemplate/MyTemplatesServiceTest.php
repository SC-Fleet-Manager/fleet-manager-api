<?php

namespace App\Tests\Acceptance\ShipTemplate;

use App\Application\Repository\ShipTemplateRepositoryInterface;
use App\Application\ShipTemplate\MyTemplatesService;
use App\Application\ShipTemplate\Output\CargoCapacityOutput;
use App\Application\ShipTemplate\Output\CrewOutput;
use App\Application\ShipTemplate\Output\ListTemplatesItemOutput;
use App\Application\ShipTemplate\Output\ListTemplatesOutput;
use App\Application\ShipTemplate\Output\ManufacturerOutput;
use App\Application\ShipTemplate\Output\PriceOutput;
use App\Application\ShipTemplate\Output\ShipChassisOutput;
use App\Domain\ShipTemplateId;
use App\Domain\TemplateAuthorId;
use App\Entity\CargoCapacity;
use App\Entity\Crew;
use App\Entity\Manufacturer;
use App\Entity\Price;
use App\Entity\ShipChassis;
use App\Entity\ShipRole;
use App\Entity\ShipSize;
use App\Entity\ShipTemplate;
use App\Infrastructure\Repository\ShipTemplate\InMemoryShipTemplateRepository;
use App\Tests\Acceptance\KernelTestCase;
use Money\Money;

class MyTemplatesServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_return_collection_of_ship_templates_of_the_logged_user(): void
    {
        $userId = TemplateAuthorId::fromString('00000000-0000-0000-0000-000000000001');

        /** @var InMemoryShipTemplateRepository $templateRepository */
        $templateRepository = static::$container->get(ShipTemplateRepositoryInterface::class);
        $templateRepository->save(new ShipTemplate(
            ShipTemplateId::fromString('00000000-0000-0000-0000-000000000010'),
            $userId,
            'Avenger Titan',
            'https://example.org/avenger.jpg',
            new ShipChassis('Avenger'),
            new Manufacturer('Robert Space Industries', 'RSI'),
            ShipSize::small(),
            new ShipRole('Combat'),
            new CargoCapacity(12),
            new Crew(1, 4),
            new Price(Money::USD(5000), Money::UEC(200000000)),
            new \DateTimeImmutable('2021-01-01 10:00:00Z'),
        ));
        $templateRepository->save(new ShipTemplate(
            ShipTemplateId::fromString('00000000-0000-0000-0000-000000000011'),
            $userId,
            'Aurora MR',
            null,
            new ShipChassis('Aurora'),
            new Manufacturer(),
            ShipSize::unknown(),
            new ShipRole(),
            new CargoCapacity(),
            new Crew(),
            new Price(),
            new \DateTimeImmutable('2021-01-02 10:00:00Z'),
        ));
        $templateRepository->save(new ShipTemplate(
            ShipTemplateId::fromString('00000000-0000-0000-0000-000000000012'),
            TemplateAuthorId::fromString('00000000-0000-0000-0000-000000000002'), // other author
            'Aurora MR',
            null,
            new ShipChassis('Aurora'),
            new Manufacturer(),
            ShipSize::unknown(),
            new ShipRole(),
            new CargoCapacity(),
            new Crew(),
            new Price(),
            new \DateTimeImmutable('2021-01-03 10:00:00Z'),
        ));

        /** @var MyTemplatesService $service */
        $service = static::$container->get(MyTemplatesService::class);
        $output = $service->handle($userId);

        static::assertEquals(new ListTemplatesOutput([
            new ListTemplatesItemOutput(
                ShipTemplateId::fromString('00000000-0000-0000-0000-000000000011'),
                'Aurora MR',
                null,
                new ShipChassisOutput('Aurora'),
                new ManufacturerOutput(),
                null,
                null,
                new CargoCapacityOutput(),
                new CrewOutput(),
                new PriceOutput(),
            ),
            new ListTemplatesItemOutput(
                ShipTemplateId::fromString('00000000-0000-0000-0000-000000000010'),
                'Avenger Titan',
                'https://example.org/avenger.jpg',
                new ShipChassisOutput('Avenger'),
                new ManufacturerOutput('Robert Space Industries', 'RSI'),
                'small',
                'Combat',
                new CargoCapacityOutput(12),
                new CrewOutput(1, 4),
                new PriceOutput(5000, 200000000),
            ),
        ]), $output);
    }

    /**
     * @test
     */
    public function it_should_return_empty_collection_if_there_is_no_created(): void
    {
        $userId = TemplateAuthorId::fromString('00000000-0000-0000-0000-000000000001');

        /** @var InMemoryShipTemplateRepository $templateRepository */
        $templateRepository = static::$container->get(ShipTemplateRepositoryInterface::class);
        $templateRepository->save(new ShipTemplate(
            ShipTemplateId::fromString('00000000-0000-0000-0000-000000000012'),
            TemplateAuthorId::fromString('00000000-0000-0000-0000-000000000002'), // other author
            'Aurora MR',
            null,
            new ShipChassis('Aurora'),
            new Manufacturer(),
            ShipSize::unknown(),
            new ShipRole(),
            new CargoCapacity(),
            new Crew(),
            new Price(),
            new \DateTimeImmutable('2021-01-03 10:00:00Z'),
        ));

        /** @var MyTemplatesService $service */
        $service = static::$container->get(MyTemplatesService::class);
        $output = $service->handle($userId);

        static::assertEquals(new ListTemplatesOutput(), $output);
    }
}
