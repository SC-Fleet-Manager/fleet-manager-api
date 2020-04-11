<?php

namespace App\Service\Ship\InfosProvider;

use App\Domain\ShipInfo;
use App\Repository\ShipChassisRepository;
use App\Repository\ShipNameRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiShipInfosProvider implements ShipInfosProviderInterface
{
    private const BASE_URL = 'https://robertsspaceindustries.com';
    private const MEDIA_URL = 'https://media.robertsspaceindustries.com';

    private LoggerInterface $logger;
    private CacheInterface $cache;
    private ShipNameRepository $shipNameRepository;
    private ShipChassisRepository $shipChassisRepository;
    private HttpClientInterface $httpClient;
    private array $ships = [];
    private array $shipNames = [];
    private array $shipNamesFlipped = [];
    private array $chassisNames = [];

    public function __construct(
        LoggerInterface $logger,
        CacheInterface $rsiShipsCache,
        HttpClientInterface $rsiShipInfosClient,
        ShipNameRepository $shipNameRepository,
        ShipChassisRepository $shipChassisRepository
    ) {
        $this->logger = $logger;
        $this->cache = $rsiShipsCache;
        $this->shipNameRepository = $shipNameRepository;
        $this->shipChassisRepository = $shipChassisRepository;
        $this->httpClient = $rsiShipInfosClient;
    }

    /**
     * @return iterable|ShipInfo[]
     */
    public function refreshShips(): array
    {
        $this->cache->delete('ship_matrix');
        $this->ships = [];

        return $this->getAllShips();
    }

    /**
     * @return iterable|ShipInfo[]
     */
    public function getAllShips(): array
    {
        if (!$this->ships) {
            $this->ships = $this->cache->get('ship_matrix', function (CacheItem $cacheItem) {
                $cacheItem->expiresAfter(new \DateInterval('P3D'));

                return $this->scrap();
            });
        }

        return $this->ships;
    }

    public function getShipsByIdOrName(array $ids, array $names = []): array
    {
        /** @var array $ships */
        $ships = $this->getAllShips();

        $res = array_filter($ships, static function (ShipInfo $shipInfo) use ($ids): bool {
            return in_array((string) $shipInfo->id, $ids, true);
        });

        foreach ($names as $name) {
            $res[] = $this->getShipByName($name);
        }

        return $res;
    }

    private function scrap(): array
    {
        $response = $this->httpClient->request('GET', '/ship-matrix/index');
        try {
            $json = $response->toArray();
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Cannot retrieve ships infos from %s.', self::BASE_URL), ['exception' => $e]);
            throw new \RuntimeException('Cannot retrieve ships infos.', 0, $e);
        }

        if (!isset($json['success']) || !$json['success']) {
            $this->logger->error(sprintf('Bad json data from %s', self::BASE_URL), ['json' => $json]);
            throw new \RuntimeException('Cannot retrieve ships infos.');
        }

        $shipInfos = [];
        foreach ($json['data'] as $shipData) {
            $shipInfo = new ShipInfo();
            $shipInfo->id = $shipData['id'];
            $shipInfo->productionStatus = $shipData['production_status'] === 'flight-ready' ? ShipInfo::FLIGHT_READY : ShipInfo::NOT_READY;
            $shipInfo->minCrew = (int) $shipData['min_crew'];
            $shipInfo->maxCrew = (int) $shipData['max_crew'];
            $shipInfo->name = trim($shipData['name']);
            $shipInfo->size = $shipData['size'];
            $shipInfo->cargoCapacity = (int) $shipData['cargocapacity'];
            $shipInfo->pledgeUrl = self::BASE_URL.$shipData['url'];
            $shipInfo->manufacturerName = $shipData['manufacturer']['name'];
            $shipInfo->manufacturerCode = $shipData['manufacturer']['code'];
            $shipInfo->chassisId = $shipData['chassis_id'];
            $shipInfo->chassisName = $this->findChassisName($shipInfo->chassisId);
            $shipInfo->mediaUrl = \count($shipData['media']) > 0 ? self::BASE_URL.$shipData['media'][0]['source_url'] ?? null : null;
            if (\count($shipData['media']) > 0) {
                $mediaUrl = $shipData['media'][0]['images']['store_small'] ?? null;
                if (strpos($mediaUrl, 'http') !== 0) { // fix some URL... Thanks Turbulent!
                    $mediaUrl = self::BASE_URL.$mediaUrl;
                }
                $shipInfo->mediaThumbUrl = $mediaUrl;
            }

            $shipInfos[$shipInfo->id] = $shipInfo;
        }

        $shipInfos = array_merge($shipInfos, $this->getSpecialCases($shipInfos));

        return $shipInfos;
    }

    private function getSpecialCases(array $officialInfos): array
    {
        $shipInfos = [];

        // add Greycat PTV
        $shipInfo = new ShipInfo();
        $shipInfo->id = '0';
        $shipInfo->productionStatus = ShipInfo::FLIGHT_READY;
        $shipInfo->minCrew = 1;
        $shipInfo->maxCrew = 2;
        $shipInfo->name = 'Greycat PTV';
        $shipInfo->size = ShipInfo::SIZE_VEHICLE;
        $shipInfo->pledgeUrl = self::BASE_URL.'/pledge/Standalone-Ships/Greycat-PTV-Buggy';
        $shipInfo->manufacturerName = 'Greycat Industrial';
        $shipInfo->manufacturerCode = 'GRIN'; // id=84
        $shipInfo->chassisId = '0';
        $shipInfo->chassisName = $this->findChassisName($shipInfo->chassisId);
        $shipInfo->mediaUrl = self::BASE_URL.'/media/5rg8z7erquf0wr/source/Buggy.jpg';
        $shipInfo->mediaThumbUrl = self::BASE_URL.'/media/5rg8z7erquf0wr/store_small/Buggy.jpg';
        $shipInfos[$shipInfo->id] = $shipInfo;

        // add F8C Lightning Civilian
        $shipInfo = new ShipInfo();
        $shipInfo->id = '1001';
        $shipInfo->productionStatus = ShipInfo::NOT_READY;
        $shipInfo->minCrew = 1;
        $shipInfo->maxCrew = 1;
        $shipInfo->name = 'F8C Lightning Civilian';
        $shipInfo->size = ShipInfo::SIZE_MEDIUM;
        $shipInfo->pledgeUrl = 'https://starcitizen.tools/F8C_Lightning';
        $shipInfo->manufacturerName = 'Anvil Aerospace';
        $shipInfo->manufacturerCode = 'ANVL'; // id=3
        $shipInfo->chassisId = '1001';
        $shipInfo->chassisName = $this->findChassisName($shipInfo->chassisId);
        $shipInfo->mediaUrl = 'https://starcitizen.tools/images/8/87/F8C_concierge.jpg';
        $shipInfo->mediaThumbUrl = 'https://starcitizen.tools/images/8/87/F8C_concierge.jpg';
        $shipInfos[$shipInfo->id] = $shipInfo;

        // add F8C Lightning Executive Edition
        $shipInfo = clone $shipInfos['1001'];
        $shipInfo->id = '1002';
        $shipInfo->name = 'F8C Lightning Executive Edition';
        $shipInfo->pledgeUrl = 'https://starcitizen.tools/F8C_Lightning_Executive_Edition';
        $shipInfo->mediaUrl = 'https://starcitizen.tools/images/1/16/F8C_Lightning_Executive_Edition.jpg';
        $shipInfo->mediaThumbUrl = 'https://starcitizen.tools/images/1/16/F8C_Lightning_Executive_Edition.jpg';
        $shipInfos[$shipInfo->id] = $shipInfo;

        // add Mustang Omega : AMD Edition
        $shipInfo = new ShipInfo();
        $shipInfo->id = '1070';
        $shipInfo->productionStatus = ShipInfo::FLIGHT_READY;
        $shipInfo->minCrew = 1;
        $shipInfo->maxCrew = 1;
        $shipInfo->name = 'Mustang Omega : AMD Edition';
        $shipInfo->size = ShipInfo::SIZE_SMALL;
        $shipInfo->pledgeUrl = 'https://robertsspaceindustries.com/pledge/ships/mustang/Mustang-Omega';
        $shipInfo->manufacturerName = 'Consolidated Outland';
        $shipInfo->manufacturerCode = 'CNOU'; // id=22
        $shipInfo->chassisId = '16';
        $shipInfo->chassisName = $this->findChassisName($shipInfo->chassisId);
        $shipInfo->mediaUrl = self::BASE_URL.'/media/gmru9y7ynd1bbr/source/Omega-Front.jpg';
        $shipInfo->mediaThumbUrl = self::BASE_URL.'/media/gmru9y7ynd1bbr/store_small/Omega-Front.jpg';
        $shipInfos[$shipInfo->id] = $shipInfo;

        // add Carrack with Pisces Expedition
        $shipInfo = clone $officialInfos['62'];
        $shipInfo->id = '1062';
        $shipInfo->name = 'Carrack with Pisces Expedition';
        $shipInfo->pledgeUrl = 'https://robertsspaceindustries.com/pledge/Standalone-Ships/Anvil-Carrack-IAE-2949';
        $shipInfo->mediaUrl = self::MEDIA_URL.'/g7dx300udpe1v/source.jpg';
        $shipInfo->mediaThumbUrl = self::MEDIA_URL.'/g7dx300udpe1v/store_small.jpg';
        $shipInfos[$shipInfo->id] = $shipInfo;

        // add Rover
        $shipInfo = new ShipInfo();
        $shipInfo->id = '1003';
        $shipInfo->productionStatus = ShipInfo::NOT_READY;
        $shipInfo->minCrew = 1;
        $shipInfo->maxCrew = 2;
        $shipInfo->name = 'Rover';
        $shipInfo->size = ShipInfo::SIZE_VEHICLE;
        $shipInfo->manufacturerName = 'Origin';
        $shipInfo->manufacturerCode = 'ORIG'; // id=6
        $shipInfo->chassisId = '1002';
        $shipInfo->chassisName = $this->findChassisName($shipInfo->chassisId);
        $shipInfos[$shipInfo->id] = $shipInfo;

        // add Dragonfly Star Kitten Edition
        $shipInfo = clone $officialInfos['112']; // Dragonfly Black
        $shipInfo->id = '1112';
        $shipInfo->name = 'Dragonfly Star Kitten Edition';
        $shipInfo->pledgeUrl = 'https://starcitizen.tools/Star_Kitten';
        $shipInfo->mediaUrl = 'https://starcitizen.tools/images/f/fb/Star_Kitten_Dragonfly.png';
        $shipInfo->mediaThumbUrl = 'https://starcitizen.tools/images/thumb/f/fb/Star_Kitten_Dragonfly.png/320px-Star_Kitten_Dragonfly.png';
        $shipInfos[$shipInfo->id] = $shipInfo;

        // add Dragonfly Star Kitten Edition
        $shipInfo = clone $officialInfos['26']; // Scythe
        $shipInfo->id = '1026';
        $shipInfo->name = 'Captured Vanduul Scythe';
        $shipInfos[$shipInfo->id] = $shipInfo;

        return $shipInfos;
    }

    public function getShipsByChassisId(string $chassisId): array
    {
        return array_filter($this->getAllShips(), static function (ShipInfo $shipInfo) use ($chassisId): bool {
            return $shipInfo->chassisId === $chassisId;
        });
    }

    public function getShipById(string $id): ?ShipInfo
    {
        $ships = $this->getAllShips();
        if (!\array_key_exists($id, $ships)) {
            return null;
        }

        return $ships[$id];
    }

    public function getShipByName(string $name): ?ShipInfo
    {
        $name = trim($name);
        $ships = $this->getAllShips();
        /** @var ShipInfo $ship */
        foreach ($ships as $ship) {
            if (mb_strtolower($ship->name) === mb_strtolower($name)) {
                return $ship;
            }
        }

        return null;
    }

    public function findChassisName(string $chassisId): string
    {
        if ($this->chassisNames === []) {
            $this->chassisNames = $this->shipChassisRepository->findAllChassisNames();
        }

        return $this->chassisNames[(int) $chassisId]['name'] ?? 'Unknown chassis';
    }

    public function shipNamesAreEquals(string $hangarName, string $providerName): bool
    {
        return $this->transformHangarToProvider(trim($hangarName)) === $providerName;
    }

    public function transformProviderToHangar(string $providerName): string
    {
        $shipNames = $this->findAllShipNamesFlipped();

        return $shipNames[$providerName]['myHangarName'] ?? $providerName;
    }

    public function transformHangarToProvider(string $hangarName): string
    {
        $shipNames = $this->findAllShipNames();

        return $shipNames[$hangarName]['shipMatrixName'] ?? $hangarName;
    }

    private function findAllShipNames(): array
    {
        if ($this->shipNames === []) {
            $this->shipNames = $this->shipNameRepository->findAllShipNames();
        }

        return $this->shipNames;
    }

    private function findAllShipNamesFlipped(): array
    {
        if ($this->shipNamesFlipped === []) {
            $shipNames = $this->findAllShipNames();
            $this->shipNamesFlipped = [];
            foreach ($shipNames as $shipName) {
                $this->shipNamesFlipped[$shipName['shipMatrixName']] = $shipName;
            }
        }

        return $this->shipNamesFlipped;
    }
}
