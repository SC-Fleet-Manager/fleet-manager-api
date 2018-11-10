<?php

namespace App\Infrastructure\Service;

use App\Domain\ShipInfo;
use App\Domain\ShipInfosProviderInterface;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class ApiShipInfosProvider implements ShipInfosProviderInterface
{
    private const BASE_URL = 'https://robertsspaceindustries.com';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CacheInterface
     */
    private $cache;

    public function __construct(LoggerInterface $logger, CacheInterface $cache)
    {
        $this->client = new Client([
            'base_uri' => self::BASE_URL,
        ]);
        $this->logger = $logger;
        $this->cache = $cache;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllShips(): iterable
    {
        if ($this->cache->has('ship_matrix')) {
            return $this->cache->get('ship_matrix');
        }

        $response = $this->client->get('/ship-matrix/index');
        $contents = $response->getBody()->getContents();
        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            $this->logger->error(sprintf('Bad response status code from %s', self::BASE_URL), [
                'status_code' => $response->getStatusCode(),
                'raw_content' => $contents,
            ]);
            throw new \RuntimeException('Cannot retrieve ships infos.');
        }
        $json = \json_decode($contents, true);
        if (!$json) {
            $this->logger->error(sprintf('Bad json response from %s', self::BASE_URL), [
                'raw_content' => $contents,
                'json_error' => json_last_error(),
                'json_error_msg' => json_last_error_msg(),
            ]);
            throw new \RuntimeException('Cannot retrieve ships infos.');
        }
        if (!isset($json['success']) || !$json['success']) {
            $this->logger->error(sprintf('Bad json data from %s', self::BASE_URL), [
                'json' => $json,
            ]);
            throw new \RuntimeException('Cannot retrieve ships infos.');
        }

        $shipInfos = [];
        foreach ($json['data'] as $shipData) {
            $shipInfo = new ShipInfo();
            $shipInfo->id = $shipData['id'];
            $shipInfo->productionStatus = $shipData['production_status'] === 'flight-ready' ? ShipInfo::FLIGHT_READY : ShipInfo::NOT_READY;
            $shipInfo->minCrew = (int) $shipData['min_crew'];
            $shipInfo->maxCrew = (int) $shipData['max_crew'];
            $shipInfo->name = $shipData['name'];
            $shipInfo->pledgeUrl = self::BASE_URL.$shipData['url'];
            $shipInfo->manufacturerName = $shipData['manufacturer']['name'];
            $shipInfo->manufacturerCode = $shipData['manufacturer']['code'];
            $shipInfo->mediaUrl = \count($shipData['media']) > 0 ? self::BASE_URL.$shipData['media'][0]['source_url'] : null;

            $shipInfos[$shipInfo->id] = $shipInfo;
        }

        $this->cache->set('ship_matrix', $shipInfos, new \DateInterval('P7D'));

        return $shipInfos;
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
        $ships = $this->getAllShips();
        /** @var ShipInfo $ship */
        foreach ($ships as $ship) {
            if ($ship->name === $name) {
                return $ship;
            }
        }

        return null;
    }
}
