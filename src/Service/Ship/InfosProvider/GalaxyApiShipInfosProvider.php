<?php

namespace App\Service\Ship\InfosProvider;

use App\Domain\ShipInfo;
use App\Repository\ShipNameRepository;
use Psr\Cache\CacheItemInterface;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Cache\TagAwareCacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GalaxyApiShipInfosProvider implements ShipInfosProviderInterface
{
    private LoggerInterface $logger;
    private TagAwareCacheInterface $cache;
    private ShipNameRepository $shipNameRepository;
    private HttpClientInterface $httpClient;
    private array $ships = [];
    private array $shipNames = [];
    private array $shipNamesFlipped = [];

    public function __construct(
        LoggerInterface $logger,
        TagAwareCacheInterface $rsiShipsCache,
        HttpClientInterface $galaxyShipInfosClient,
        ShipNameRepository $shipNameRepository
    ) {
        $this->logger = $logger;
        $this->cache = $rsiShipsCache;
        $this->shipNameRepository = $shipNameRepository;
        $this->httpClient = $galaxyShipInfosClient;
    }

    /**
     * @return iterable|ShipInfo[]
     */
    public function refreshShips(): iterable
    {
        $this->cache->invalidateTags(['galaxy_api_ship']);
        $this->ships = [];

        return $this->getAllShips();
    }

    /**
     * @return iterable|ShipInfo[]
     */
    public function getAllShips(bool $indexedById = false): iterable
    {
        if (!$this->ships) {
            $this->ships = $this->cache->get('galaxy_api_all_ships', function (ItemInterface $cacheItem) use ($indexedById) {
                $cacheItem->expiresAfter(new \DateInterval('P1D'))->tag('galaxy_api_ship');

                return $this->retrieveData($indexedById);
            });
        }

        return $this->ships;
    }

    /**
     * @return iterable|ShipInfo[]
     */
    public function getShipsByIdOrName(array $ids, array $names = []): iterable
    {
        $cacheKeysIds = [];
        foreach ($ids as $id) {
            $cacheKeysIds['galaxy_api_ship.'.$id] = $id;
        }
        $cacheKeysNames = [];
        foreach ($names as $name) {
            $cacheKeysNames['galaxy_api_ship_name.'.sha1($name)] = $name;
        }

        $missShipInfoProviderIds = [];
        $missShipInfoProviderNames = [];
        $shipInfos = [];
        $shipInfos = array_merge($shipInfos, $this->checkCacheHitShipInfo($cacheKeysIds, $missShipInfoProviderIds));
        $shipInfos = array_merge($shipInfos, $this->checkCacheHitShipInfo($cacheKeysNames, $missShipInfoProviderNames));

        // if there are some missing ship infos, let's request them.
        if ($missShipInfoProviderIds !== [] || $missShipInfoProviderNames !== []) {
            $response = $this->httpClient->request('POST', '/api/ships/bulk', [
                'query' => [
                    'pagination' => 'false',
                ],
                'json' => [
                    'ids' => array_keys($missShipInfoProviderIds),
                    'names' => array_keys($missShipInfoProviderNames),
                ],
            ]);
            try {
                $json = $response->toArray();
            } catch (\Exception $e) {
                $this->logger->error(sprintf('Cannot retrieve ships infos from Galaxy : {message}.'), ['exception' => $e, 'message' => $e->getMessage()]);
                throw new \RuntimeException('Cannot retrieve ships infos.', 0, $e);
            }

            foreach ($json as $shipJson) {
                $shipInfo = $this->createShipInfo($shipJson);
                $shipInfos[$shipInfo->id] = $shipInfo;

                $callback = static function (ItemInterface $item) use ($shipInfo) {
                    $item->expiresAfter(3600)->tag('galaxy_api_ship');

                    return $shipInfo;
                };
                $this->cache->get('galaxy_api_ship_name.'.sha1(mb_strtolower($shipInfo->name)), $callback, INF);
                $this->cache->get('galaxy_api_ship.'.$shipInfo->id, $callback, INF);
            }
        }

        return $shipInfos;
    }

    /**
     * @param string[]             $cacheKeys                 hashmap ['cache key' => 'field value']
     * @param CacheItemInterface[] $missShipInfoProviderField hashmap ['field value' => cache item]
     *
     * @return ShipInfo[]
     */
    private function checkCacheHitShipInfo(array $cacheKeys, array &$missShipInfoProviderField): array
    {
        $shipInfos = [];
        /** @var CacheItemInterface[] $shipInfoItems */
        $shipInfoItems = $this->cache->getItems(array_keys($cacheKeys));
        foreach ($shipInfoItems as $shipInfoItem) {
            if (!$shipInfoItem->isHit()) {
                $missShipInfoProviderField[$cacheKeys[$shipInfoItem->getKey()]] = $shipInfoItem;
                continue;
            }
            /** @var ShipInfo $shipInfo */
            $shipInfo = $shipInfoItem->get();
            $shipInfos[$shipInfo->id] = $shipInfo;
        }

        return $shipInfos;
    }

    private function createShipInfo(array $apiResponse): ShipInfo
    {
        $shipInfo = new ShipInfo();
        $shipInfo->id = $apiResponse['id'];
        $shipInfo->productionStatus = $apiResponse['readyStatus'] === 'flight-ready' ? ShipInfo::FLIGHT_READY : ShipInfo::NOT_READY;
        $shipInfo->minCrew = isset($apiResponse['minCrew']) ? (int) $apiResponse['minCrew'] : null;
        $shipInfo->maxCrew = isset($apiResponse['maxCrew']) ? (int) $apiResponse['maxCrew'] : null;
        $shipInfo->name = $apiResponse['name'];
        $shipInfo->size = $apiResponse['size'];
        $shipInfo->cargoCapacity = isset($apiResponse['cargoCapacity']) ? (int) $apiResponse['cargoCapacity'] : null;
        $shipInfo->pledgeUrl = $apiResponse['pledgeUrl'] ?? null;
        $shipInfo->chassisId = $apiResponse['chassis']['id'] ?? null;
        $shipInfo->chassisName = $apiResponse['chassis']['name'] ?? null;
        $shipInfo->manufacturerId = $apiResponse['chassis']['manufacturer']['id'] ?? null;
        $shipInfo->manufacturerName = $apiResponse['chassis']['manufacturer']['name'] ?? null;
        $shipInfo->manufacturerCode = $apiResponse['chassis']['manufacturer']['code'] ?? null;
        if (isset($apiResponse['pictureUri'])) {
            $shipInfo->mediaUrl = $apiResponse['pictureUri'];
        }
        if (isset($apiResponse['thumbnailUri'])) {
            $shipInfo->mediaThumbUrl = $apiResponse['thumbnailUri'];
        }

        return $shipInfo;
    }

    /**
     * @return ShipInfo[]
     */
    private function retrieveData(bool $indexedById = false): array
    {
        $response = $this->httpClient->request('GET', '/api/ships', [
            'query' => [
                'pagination' => 'false',
            ],
        ]);
        try {
            $json = $response->toArray();
        } catch (\Exception $e) {
            $this->logger->error(sprintf('Cannot retrieve ships infos from Galaxy : {message}.'), ['exception' => $e, 'message' => $e->getMessage()]);
            throw new \RuntimeException('Cannot retrieve ships infos.', 0, $e);
        }

        $shipInfos = [];
        foreach ($json as $shipJson) {
            $shipInfo = $this->createShipInfo($shipJson);

            if ($indexedById) {
                $shipInfos[$shipInfo->id] = $shipInfo;
            } else {
                $shipInfos[] = $shipInfo;
            }
        }

        return $shipInfos;
    }

    public function getShipsByChassisId(string $chassisId): iterable
    {
        return array_filter($this->getAllShips(), static function (ShipInfo $shipInfo) use ($chassisId): bool {
            return $shipInfo->chassisId === $chassisId;
        });
    }

    public function getShipById(string $id): ?ShipInfo
    {
        $ships = $this->getAllShips(true);
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
