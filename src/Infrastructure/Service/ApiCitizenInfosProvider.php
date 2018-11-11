<?php

namespace App\Infrastructure\Service;

use App\Domain\CitizenInfos;
use App\Domain\CitizenInfosProviderInterface;
use App\Domain\CitizenNumber;
use App\Domain\Exception\NotFoundHandleSCException;
use App\Domain\HandleSC;
use App\Domain\Trigram;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;

class ApiCitizenInfosProvider implements CitizenInfosProviderInterface
{
    private const BASE_URL = 'http://sc-api.com';

    /**
     * @var Client
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->client = new Client([
            'base_uri' => self::BASE_URL,
        ]);
        $this->logger = $logger;
    }

    public function retrieveInfos(HandleSC $handleSC): CitizenInfos
    {
        $response = $this->client->get('/', [
            'query' => [
                'api_source' => 'live',
                'system' => 'accounts',
                'action' => 'full_profile',
                'target_id' => (string) $handleSC,
            ],
        ]);

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            $this->logger->error(sprintf('Bad response status code from %s', self::BASE_URL), [
                'status_code' => $response->getStatusCode(),
                'raw_content' => $response->getBody()->getContents(),
            ]);
            throw new \RuntimeException('Cannot retrieve citizen infos.');
        }

        $contents = $response->getBody()->getContents();
        $json = \json_decode($contents, true);
        if (!$json) {
            $this->logger->error(sprintf('Bad json response from %s', self::BASE_URL), [
                'raw_content' => $contents,
                'json_error' => json_last_error(),
                'json_error_msg' => json_last_error_msg(),
            ]);
            throw new \RuntimeException('Cannot retrieve citizen infos.');
        }
        if (!isset($json['data'])) {
            $this->logger->error(sprintf('Handle %s does not exist', (string) $handleSC), [
                'payload' => $json,
            ]);
            throw new NotFoundHandleSCException(sprintf('Handle %s does not exist', (string) $handleSC));
        }

        $this->logger->info('Citizen infos retrieved.', [
            'handle' => $json['data']['handle'],
            'citizen_number' => $json['data']['citizen_number'],
            'payload' => $json['data'],
        ]);

        $ci = new CitizenInfos(
            new CitizenNumber($json['data']['citizen_number']),
            new HandleSC($json['data']['handle'])
        );
        if ($json['data']['organizations'] !== null) {
            $ci->organisations = array_map(function (array $orga): Trigram {
                return new Trigram($orga['sid']);
            }, $json['data']['organizations']);
        }
        $ci->avatarUrl = $json['data']['avatar'];
        $ci->registered = \DateTimeImmutable::createFromFormat('U', $json['data']['enlisted']);

        return $ci;
    }
}
