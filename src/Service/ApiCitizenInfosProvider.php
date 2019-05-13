<?php

namespace App\Service;

use App\Domain\CitizenInfos;
use App\Domain\CitizenNumber;
use App\Domain\HandleSC;
use App\Domain\SpectrumIdentification;
use App\Exception\NotFoundHandleSCException;
use Goutte\Client as GoutteClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\DomCrawler\Crawler;

class ApiCitizenInfosProvider implements CitizenInfosProviderInterface
{
    private const BASE_URL = 'https://robertsspaceindustries.com';

    /**
     * @var GoutteClient
     */
    private $client;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->client = new GoutteClient();
        $this->logger = $logger;
    }

    public function retrieveInfos(HandleSC $handleSC): CitizenInfos
    {
        $crawler = $this->client->request('GET', self::BASE_URL.'/citizens/'.$handleSC);
        $profileCrawler = $crawler->filter('#public-profile');

        $avatarUrl = null;
        $avatarCrawler = $profileCrawler->filter('.profile .thumb img');
        if ($avatarCrawler->count() > 0) {
            $avatarUrl = self::BASE_URL.$avatarCrawler->attr('src');
        }

        $citizenNumber = null;
        $citizenNumberCrawler = $profileCrawler->filter('.citizen-record .value');
        if ($citizenNumberCrawler->count() > 0) {
            $citizenNumber = preg_replace('/[^0-9]/', '', $citizenNumberCrawler->text());
        }

        $enlisted = null;
        $enlistedCrawler = $profileCrawler->filterXPath('//p[contains(.//*/text(), "Enlisted")]/*[contains(@class, "value")]');
        if ($enlistedCrawler->count() > 0) {
            $enlisted = \DateTimeImmutable::createFromFormat('F j, Y', $enlistedCrawler->text())->setTime(0, 0);
        }

        $bio = null;
        $bioCrawler = $profileCrawler->filter('.bio .value');
        if ($bioCrawler->count() > 0) {
            $bio = trim($bioCrawler->text());
        }

        $sids = [];
        $crawler = $this->client->request('GET', self::BASE_URL.'/citizens/'.$handleSC.'/organizations');
        $sidCrawler = $crawler->filterXPath('//p[contains(.//*/text(), "Spectrum Identification (SID)")]/*[contains(@class, "value")]');
        if ($sidCrawler->count() > 0) {
            $sids = $sidCrawler->each(function (Crawler $node) {
                return $node->text();
            });
        }

        if ($citizenNumber === null) {
            $this->logger->error(sprintf('Handle %s does not exist', (string) $handleSC), []);
            throw new NotFoundHandleSCException(sprintf('Handle %s does not exist', (string) $handleSC));
        }

        $ci = new CitizenInfos(
            new CitizenNumber($citizenNumber),
            clone $handleSC
        );
        $ci->organisations = array_map(function (string $sid): SpectrumIdentification {
            return new SpectrumIdentification($sid);
        }, $sids);
        $ci->bio = $bio;
        $ci->avatarUrl = $avatarUrl;
        $ci->registered = $enlisted;

        $this->logger->info('Citizen infos retrieved.', [
            'handle' => (string) $handleSC,
            'citizen_number' => $citizenNumber,
        ]);

        return $ci;
    }
}
