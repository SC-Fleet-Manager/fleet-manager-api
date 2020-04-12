<?php

namespace Tests\App\Service\Ship\InfosProvider;

use App\Domain\ShipInfo;
use App\Service\Ship\InfosProvider\GalaxyApiShipInfosProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\TagAwareAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GalaxyApiShipInfosProviderTest extends TestCase
{
    /**
     * @group unit
     */
    public function test_get_all_ships_success(): void
    {
        $httpClient = $this->createMockHttpClient();
        $cache = new TagAwareAdapter(new ArrayAdapter());

        $provider = new GalaxyApiShipInfosProvider(new NullLogger(), $cache, $httpClient);

        /** @var ShipInfo[] $ships */
        $ships = $provider->getAllShips();

        $this->assertCount(2, $ships);
        $this->assertSame('00781e7d-8919-4240-bd00-ca09e7346944', $ships['00781e7d-8919-4240-bd00-ca09e7346944']->id);
        $this->assertSame(ShipInfo::FLIGHT_READY, $ships['00781e7d-8919-4240-bd00-ca09e7346944']->productionStatus);
        $this->assertSame(1, $ships['00781e7d-8919-4240-bd00-ca09e7346944']->minCrew);
        $this->assertSame(2, $ships['00781e7d-8919-4240-bd00-ca09e7346944']->maxCrew);
        $this->assertSame('Greycat PTV', $ships['00781e7d-8919-4240-bd00-ca09e7346944']->name);
        $this->assertSame(ShipInfo::SIZE_VEHICLE, $ships['00781e7d-8919-4240-bd00-ca09e7346944']->size);
        $this->assertNull($ships['00781e7d-8919-4240-bd00-ca09e7346944']->cargoCapacity);
        $this->assertNull($ships['00781e7d-8919-4240-bd00-ca09e7346944']->pledgeUrl);
        $this->assertSame('44e37734-b017-4800-956d-7a2092847687', $ships['00781e7d-8919-4240-bd00-ca09e7346944']->manufacturerId);
        $this->assertSame('Greycat Industrial', $ships['00781e7d-8919-4240-bd00-ca09e7346944']->manufacturerName);
        $this->assertSame('GRIN', $ships['00781e7d-8919-4240-bd00-ca09e7346944']->manufacturerCode);
        $this->assertSame('128c0ee3-6d56-43c5-967f-4e56bfde9ec1', $ships['00781e7d-8919-4240-bd00-ca09e7346944']->chassisId);
        $this->assertSame('Greycat PTV', $ships['00781e7d-8919-4240-bd00-ca09e7346944']->chassisName);
        $this->assertSame('https://mock.sc-galaxy.com/uploads/cache/pictures/greycat-ptv.png', $ships['00781e7d-8919-4240-bd00-ca09e7346944']->mediaUrl);
        $this->assertSame('https://mock.sc-galaxy.com/uploads/cache/thumbnails/greycat-ptv.png', $ships['00781e7d-8919-4240-bd00-ca09e7346944']->mediaThumbUrl);

        $this->assertSame('25f19915-f9eb-4b87-9efe-cea701664e38', $ships['25f19915-f9eb-4b87-9efe-cea701664e38']->id);
        $this->assertSame('Carrack Expedition', $ships['25f19915-f9eb-4b87-9efe-cea701664e38']->name);

        $this->assertTrue($cache->hasItem('galaxy_api_all_ships'), 'cache has no key "galaxy_api_all_ships"');
        $item = $cache->getItem('galaxy_api_all_ships')->get();
        $this->assertCount(2, $item);
        $this->assertSame('00781e7d-8919-4240-bd00-ca09e7346944', $item['00781e7d-8919-4240-bd00-ca09e7346944']->id);
        $this->assertSame('25f19915-f9eb-4b87-9efe-cea701664e38', $item['25f19915-f9eb-4b87-9efe-cea701664e38']->id);
    }

    /**
     * @group unit
     */
    public function test_get_ships_by_id_or_name_success_without_cache(): void
    {
        $httpClient = $this->createMockHttpClient();
        $cache = new TagAwareAdapter(new ArrayAdapter());

        $provider = new GalaxyApiShipInfosProvider(new NullLogger(), $cache, $httpClient);

        /** @var ShipInfo[] $ships */
        $ships = $provider->getShipsByIdOrName(['25f19915-f9eb-4b87-9efe-cea701664e38'], ['Greycat PTV']);
        $this->assertCount(2, $ships);
        $this->assertSame('00781e7d-8919-4240-bd00-ca09e7346944', $ships['00781e7d-8919-4240-bd00-ca09e7346944']->id);
        $this->assertSame('Greycat PTV', $ships['00781e7d-8919-4240-bd00-ca09e7346944']->name);
        $this->assertSame('25f19915-f9eb-4b87-9efe-cea701664e38', $ships['25f19915-f9eb-4b87-9efe-cea701664e38']->id);
        $this->assertSame('Carrack Expedition', $ships['25f19915-f9eb-4b87-9efe-cea701664e38']->name);

        $this->assertTrue($cache->hasItem('galaxy_api_ship.00781e7d-8919-4240-bd00-ca09e7346944'), 'cache has a missing key');
        /** @var ShipInfo $item */
        $item = $cache->getItem('galaxy_api_ship.00781e7d-8919-4240-bd00-ca09e7346944')->get();
        $this->assertSame('00781e7d-8919-4240-bd00-ca09e7346944', $item->id);
        $this->assertSame('Greycat PTV', $item->name);
        $this->assertTrue($cache->hasItem('galaxy_api_ship_name.'.sha1('greycat ptv')), 'cache has a missing key');
        $item = $cache->getItem('galaxy_api_ship_name.'.sha1('greycat ptv'))->get();
        $this->assertSame('00781e7d-8919-4240-bd00-ca09e7346944', $item->id);
        $this->assertSame('Greycat PTV', $item->name);

        $this->assertTrue($cache->hasItem('galaxy_api_ship.25f19915-f9eb-4b87-9efe-cea701664e38'), 'cache has a missing key');
        $item = $cache->getItem('galaxy_api_ship.25f19915-f9eb-4b87-9efe-cea701664e38')->get();
        $this->assertSame('25f19915-f9eb-4b87-9efe-cea701664e38', $item->id);
        $this->assertSame('Carrack Expedition', $item->name);
        $this->assertTrue($cache->hasItem('galaxy_api_ship_name.'.sha1('carrack expedition')), 'cache has a missing key');
        $item = $cache->getItem('galaxy_api_ship_name.'.sha1('carrack expedition'))->get();
        $this->assertSame('25f19915-f9eb-4b87-9efe-cea701664e38', $item->id);
        $this->assertSame('Carrack Expedition', $item->name);
    }

    /**
     * @group unit
     */
    public function test_get_ships_by_id_or_name_success_with_cache(): void
    {
        $httpClient = $this->getMockBuilder(HttpClientInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $httpClient->expects($this->never())->method('request');

        $cache = new TagAwareAdapter(new ArrayAdapter());
        $item = $cache->getItem('galaxy_api_ship.25f19915-f9eb-4b87-9efe-cea701664e38');
        $shipInfo = new ShipInfo();
        $shipInfo->id = '25f19915-f9eb-4b87-9efe-cea701664e38';
        $shipInfo->name = 'Carrack Expedition';
        $item->set($shipInfo);
        $cache->save($item);
        $item = $cache->getItem('galaxy_api_ship_name.'.sha1('greycat ptv'));
        $shipInfo = new ShipInfo();
        $shipInfo->id = '00781e7d-8919-4240-bd00-ca09e7346944';
        $shipInfo->name = 'Greycat PTV';
        $item->set($shipInfo);
        $cache->save($item);

        $provider = new GalaxyApiShipInfosProvider(new NullLogger(), $cache, $httpClient);

        /** @var ShipInfo[] $ships */
        $ships = $provider->getShipsByIdOrName(['25f19915-f9eb-4b87-9efe-cea701664e38'], ['Greycat PTV']);

        $this->assertCount(2, $ships);
        $this->assertSame('00781e7d-8919-4240-bd00-ca09e7346944', $ships['00781e7d-8919-4240-bd00-ca09e7346944']->id);
        $this->assertSame('Greycat PTV', $ships['00781e7d-8919-4240-bd00-ca09e7346944']->name);
        $this->assertSame('25f19915-f9eb-4b87-9efe-cea701664e38', $ships['25f19915-f9eb-4b87-9efe-cea701664e38']->id);
        $this->assertSame('Carrack Expedition', $ships['25f19915-f9eb-4b87-9efe-cea701664e38']->name);
    }

    /**
     * @group unit
     */
    public function test_get_ships_by_chassis_id_success(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse(\json_encode([
                [
                    'id' => '5015ae47-0bbf-40e5-8b04-71cdd8590b6a',
                    'name' => 'Aurora LN',
                    'chassis' => [
                        'id' => '044637ff-364f-4bc0-ab08-e18c29ce716c',
                        'name' => 'Aurora',
                        'manufacturer' => [
                            'id' => 'f2ebc5cd-da4b-4756-82e0-0de284790d3c',
                            'name' => 'Robert Space Industries',
                            'code' => 'RSI',
                            'logoUri' => 'https://sc-galaxy.traefik.test/media/cache/resolve/logos/robert-space-industries.png',
                        ],
                    ],
                    'holdedShips' => [],
                    'loanerShips' => [],
                    'height' => 4,
                    'length' => 18,
                    'beam' => null,
                    'maxCrew' => 1,
                    'minCrew' => 1,
                    'readyStatus' => 'flight-ready',
                    'size' => 'small',
                    'cargoCapacity' => 0,
                    'career' => [
                        'id' => '2caf6940-9405-4eff-8a97-1f75412e51ce',
                        'label' => 'Combat',
                    ],
                    'roles' => [
                        [
                            'id' => '8e30b822-25a6-4716-8429-216b300115bd',
                            'label' => 'Fighters',
                        ],
                    ],
                    'pledgeUrl' => 'https://robertsspaceindustries.com/pledge/ships/rsi-aurora/Aurora-LN',
                    'pledgeCost' => 3500,
                    'createdAt' => '2020-01-11T01:49:49+00:00',
                    'updatedAt' => '2020-01-11T01:49:49+00:00',
                    'pictureUri' => 'https://sc-galaxy.traefik.test/media/cache/resolve/pictures/aurora-ln.jpeg',
                    'thumbnailUri' => 'https://sc-galaxy.traefik.test/uploads/cache/thumbnails/aurora-ln.jpeg',
                ],
                [
                    'id' => '5a6520b3-d155-4994-a1b3-8584efa2683e',
                    'name' => 'Aurora LX',
                    'chassis' => [
                        'id' => '044637ff-364f-4bc0-ab08-e18c29ce716c',
                        'name' => 'Aurora',
                        'manufacturer' => [
                            'id' => 'f2ebc5cd-da4b-4756-82e0-0de284790d3c',
                            'name' => 'Robert Space Industries',
                            'code' => 'RSI',
                            'logoUri' => 'https://sc-galaxy.traefik.test/media/cache/resolve/logos/robert-space-industries.png',
                        ],
                    ],
                    'holdedShips' => [],
                    'loanerShips' => [],
                    'height' => 4,
                    'length' => 18,
                    'beam' => null,
                    'maxCrew' => 1,
                    'minCrew' => 1,
                    'readyStatus' => 'flight-ready',
                    'size' => 'small',
                    'cargoCapacity' => 0,
                    'career' => [
                        'id' => '9ed86581-5d55-40bc-bfbc-df08e37750a6',
                        'label' => 'Exploration',
                    ],
                    'roles' => [
                        [
                            'id' => '922f426f-dd12-4d2a-9fdb-19c5a9cb2417',
                            'label' => 'Pathfinder',
                        ],
                    ],
                    'pledgeUrl' => null,
                    'pledgeCost' => null,
                    'createdAt' => '2020-01-11T01:43:03+00:00',
                    'updatedAt' => '2020-01-11T01:52:59+00:00',
                    'pictureUri' => 'https://sc-galaxy.traefik.test/media/cache/resolve/pictures/aurora-lx.jpeg',
                    'thumbnailUri' => 'https://sc-galaxy.traefik.test/media/cache/resolve/thumbnails/aurora-lx.jpeg',
                ],
            ])),
        ], 'https://mock.sc-galaxy.com');
        $cache = new TagAwareAdapter(new ArrayAdapter());

        $provider = new GalaxyApiShipInfosProvider(new NullLogger(), $cache, $httpClient);

        /** @var ShipInfo[] $ships */
        $ships = $provider->getShipsByChassisId('044637ff-364f-4bc0-ab08-e18c29ce716c');
        $this->assertCount(2, $ships);
        $this->assertSame('5015ae47-0bbf-40e5-8b04-71cdd8590b6a', $ships['5015ae47-0bbf-40e5-8b04-71cdd8590b6a']->id);
        $this->assertSame('Aurora LN', $ships['5015ae47-0bbf-40e5-8b04-71cdd8590b6a']->name);
        $this->assertSame('5a6520b3-d155-4994-a1b3-8584efa2683e', $ships['5a6520b3-d155-4994-a1b3-8584efa2683e']->id);
        $this->assertSame('Aurora LX', $ships['5a6520b3-d155-4994-a1b3-8584efa2683e']->name);

        $this->assertTrue($cache->hasItem('galaxy_api_ships_chassis.044637ff-364f-4bc0-ab08-e18c29ce716c'), 'cache has a missing key');
        /** @var ShipInfo[] $item */
        $item = $cache->getItem('galaxy_api_ships_chassis.044637ff-364f-4bc0-ab08-e18c29ce716c')->get();
        $this->assertSame('5015ae47-0bbf-40e5-8b04-71cdd8590b6a', $item['5015ae47-0bbf-40e5-8b04-71cdd8590b6a']->id);
        $this->assertSame('Aurora LN', $item['5015ae47-0bbf-40e5-8b04-71cdd8590b6a']->name);
        $this->assertSame('5a6520b3-d155-4994-a1b3-8584efa2683e', $item['5a6520b3-d155-4994-a1b3-8584efa2683e']->id);
        $this->assertSame('Aurora LX', $item['5a6520b3-d155-4994-a1b3-8584efa2683e']->name);
    }

    /**
     * @group unit
     */
    public function test_get_ship_by_id_success(): void
    {
        $httpClient = new MockHttpClient([
            new MockResponse(\json_encode([
                'id' => '5015ae47-0bbf-40e5-8b04-71cdd8590b6a',
                'name' => 'Aurora LN',
                'chassis' => [
                    'id' => '044637ff-364f-4bc0-ab08-e18c29ce716c',
                    'name' => 'Aurora',
                    'manufacturer' => [
                        'id' => 'f2ebc5cd-da4b-4756-82e0-0de284790d3c',
                        'name' => 'Robert Space Industries',
                        'code' => 'RSI',
                        'logoUri' => 'https://sc-galaxy.traefik.test/media/cache/resolve/logos/robert-space-industries.png',
                    ],
                ],
                'holdedShips' => [],
                'loanerShips' => [],
                'height' => 4,
                'length' => 18,
                'beam' => null,
                'maxCrew' => 1,
                'minCrew' => 1,
                'readyStatus' => 'flight-ready',
                'size' => 'small',
                'cargoCapacity' => 0,
                'career' => [
                    'id' => '2caf6940-9405-4eff-8a97-1f75412e51ce',
                    'label' => 'Combat',
                ],
                'roles' => [
                    [
                        'id' => '8e30b822-25a6-4716-8429-216b300115bd',
                        'label' => 'Fighters',
                    ],
                ],
                'pledgeUrl' => 'https://robertsspaceindustries.com/pledge/ships/rsi-aurora/Aurora-LN',
                'pledgeCost' => 3500,
                'createdAt' => '2020-01-11T01:49:49+00:00',
                'updatedAt' => '2020-01-11T01:49:49+00:00',
                'pictureUri' => 'https://sc-galaxy.traefik.test/media/cache/resolve/pictures/aurora-ln.jpeg',
                'thumbnailUri' => 'https://sc-galaxy.traefik.test/uploads/cache/thumbnails/aurora-ln.jpeg',
            ])),
        ], 'https://mock.sc-galaxy.com');
        $cache = new TagAwareAdapter(new ArrayAdapter());

        $provider = new GalaxyApiShipInfosProvider(new NullLogger(), $cache, $httpClient);

        $ship = $provider->getShipById('5015ae47-0bbf-40e5-8b04-71cdd8590b6a');

        $this->assertSame('5015ae47-0bbf-40e5-8b04-71cdd8590b6a', $ship->id);
        $this->assertSame('Aurora LN', $ship->name);

        $this->assertTrue($cache->hasItem('galaxy_api_ship.5015ae47-0bbf-40e5-8b04-71cdd8590b6a'), 'cache has a missing key');
        /** @var ShipInfo $item */
        $item = $cache->getItem('galaxy_api_ship.5015ae47-0bbf-40e5-8b04-71cdd8590b6a')->get();
        $this->assertSame('5015ae47-0bbf-40e5-8b04-71cdd8590b6a', $item->id);
        $this->assertSame('Aurora LN', $item->name);
    }

    private function createMockHttpClient(): HttpClientInterface
    {
        return new MockHttpClient([
            new MockResponse(\json_encode([
                [
                    'id' => '00781e7d-8919-4240-bd00-ca09e7346944',
                    'name' => 'Greycat PTV',
                    'chassis' => [
                        'id' => '128c0ee3-6d56-43c5-967f-4e56bfde9ec1',
                        'name' => 'Greycat PTV',
                        'manufacturer' => [
                            'id' => '44e37734-b017-4800-956d-7a2092847687',
                            'name' => 'Greycat Industrial',
                            'code' => 'GRIN',
                            'logoUri' => 'https://mock.sc-galaxy.com/uploads/cache/logos/greycat-industrial.jpeg',
                        ],
                    ],
                    'holdedShips' => [],
                    'loanerShips' => [],
                    'height' => null,
                    'length' => null,
                    'beam' => null,
                    'maxCrew' => 2,
                    'minCrew' => 1,
                    'readyStatus' => 'flight-ready',
                    'size' => 'vehicle',
                    'cargoCapacity' => null,
                    'career' => null,
                    'roles' => [],
                    'pledgeUrl' => null,
                    'pledgeCost' => 1500,
                    'createdAt' => '2020-04-07T19:04:27+00:00',
                    'updatedAt' => '2020-04-07T19:04:27+00:00',
                    'pictureUri' => 'https://mock.sc-galaxy.com/uploads/cache/pictures/greycat-ptv.png',
                    'thumbnailUri' => 'https://mock.sc-galaxy.com/uploads/cache/thumbnails/greycat-ptv.png',
                ],
                [
                    'id' => '25f19915-f9eb-4b87-9efe-cea701664e38',
                    'name' => 'Carrack Expedition',
                    'chassis' => [
                        'id' => 'c35f0fec-515c-4118-a3e2-997dc14d12f7',
                        'name' => 'Carrack',
                        'manufacturer' => [
                            'id' => 'a4a403e6-201c-4157-a425-420ec93dc77f',
                            'name' => 'Anvil Aerospace',
                            'code' => 'ANVL',
                            'logoUri' => 'https://mock.sc-galaxy.com/uploads/cache/logos/anvil-aerospace.png',
                        ],
                    ],
                    'holdedShips' => [],
                    'loanerShips' => [],
                    'height' => 30,
                    'length' => 126.5,
                    'beam' => 76.5,
                    'maxCrew' => 6,
                    'minCrew' => 4,
                    'readyStatus' => 'flight-ready',
                    'size' => 'large',
                    'cargoCapacity' => 456,
                    'career' => [
                        'id' => '9ed86581-5d55-40bc-bfbc-df08e37750a6',
                        'label' => 'Exploration',
                    ],
                    'roles' => [
                        [
                            'id' => 'cef8fc55-33b5-40e3-b093-f63c4ad8428d',
                            'label' => 'Expedition',
                        ],
                    ],
                    'pledgeUrl' => 'https://robertsspaceindustries.com/pledge/ships/carrack/Carrack-Expedition-W-C8X',
                    'pledgeCost' => 64500,
                    'createdAt' => '2020-02-28T08:38:55+00:00',
                    'updatedAt' => '2020-02-28T08:38:56+00:00',
                    'pictureUri' => 'https://mock.sc-galaxy.com/uploads/cache/pictures/carrack-expedition.jpeg',
                    'thumbnailUri' => null,
                ],
            ])),
        ], 'https://mock.sc-galaxy.com');
    }
}
