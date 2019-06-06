<?php

namespace App\Service;

use App\Domain\CitizenInfos;
use App\Domain\CitizenNumber;
use App\Domain\CitizenOrganizationInfo;
use App\Domain\HandleSC;
use App\Domain\SpectrumIdentification;
use App\Exception\NotFoundHandleSCException;
use Goutte\Client as GoutteClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class ApiCitizenInfosProvider implements CitizenInfosProviderInterface
{
    private const BASE_URL = 'https://robertsspaceindustries.com';

    /** @var GoutteClient */
    private $client;
    private $logger;
    private $cache;
    private $serializer;

    public function __construct(LoggerInterface $logger, CacheInterface $cache, SerializerInterface $serializer)
    {
        $this->client = new GoutteClient();
        $this->logger = $logger;
        $this->cache = $cache;
        $this->serializer = $serializer;
    }

    public function retrieveInfos(HandleSC $handleSC, bool $caching = true): CitizenInfos
    {
        return $this->cache->get('citizen_info_'.$handleSC, function (CacheItem $cacheItem) use ($handleSC) {
            $cacheItem->tag(['citizen_infos']);
            $cacheItem->expiresAfter(1200); // 20min

            return $this->scrap($handleSC);
        }, $caching ? null : INF);
    }

    private function scrap(HandleSC $handleSC): CitizenInfos
    {
        $crawler = $this->client->request('GET', self::BASE_URL.'/citizens/'.$handleSC);
        $profileCrawler = $crawler->filter('#public-profile');

        $citizenNumber = null;
        $citizenNumberCrawler = $profileCrawler->filter('.citizen-record .value');
        if ($citizenNumberCrawler->count() > 0) {
            $citizenNumber = preg_replace('/[^0-9]/', '', $citizenNumberCrawler->text());
        }

        if ($citizenNumber === null) {
            $this->logger->error(sprintf('Handle %s does not exist', (string) $handleSC), []);
            throw new NotFoundHandleSCException(sprintf('Handle %s does not exist', (string) $handleSC));
        }

        $nickname = null;
        $nicknameCrawler = $profileCrawler->filter('.profile .info .entry:first-child .value');
        if ($nicknameCrawler->count() > 0) {
            $nickname = trim($nicknameCrawler->text());
        }

        $avatarUrl = null;
        $avatarCrawler = $profileCrawler->filter('.profile .thumb img');
        if ($avatarCrawler->count() > 0) {
            $avatarUrl = self::BASE_URL.$avatarCrawler->attr('src');
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

        $crawler = $this->client->request('GET', self::BASE_URL.'/citizens/'.$handleSC.'/organizations');
        $mainOrga = null;
        $mainOrgaCrawler = $crawler->filter('.org.main.visibility-V');
        if ($mainOrgaCrawler->count() > 0) {
            $sid = $mainOrgaCrawler->filterXPath('//p[contains(.//*/text(), "Spectrum Identification (SID)")]/*[contains(@class, "value")]')->text();
            $rankName = $mainOrgaCrawler->filterXPath('//p[contains(.//*/text(), "Organization rank")]/*[contains(@class, "value")]')->text();
            $rank = $mainOrgaCrawler->filter('.ranking .active')->count();

            $mainOrga = new CitizenOrganizationInfo(new SpectrumIdentification($sid), $rank, $rankName);
        }
        $orgaAffiliates = [];
        $crawler->filter('.org.affiliation.visibility-V')->each(static function (Crawler $node) use (&$orgaAffiliates) {
            $sid = $node->filterXPath('//p[contains(.//*/text(), "Spectrum Identification (SID)")]/*[contains(@class, "value")]')->text();
            $rankName = $node->filterXPath('//p[contains(.//*/text(), "Organization rank")]/*[contains(@class, "value")]')->text();
            $rank = $node->filter('.ranking .active')->count();

            $orga = new CitizenOrganizationInfo(new SpectrumIdentification($sid), $rank, $rankName);
            $orgaAffiliates[] = $orga;
        });

        $ci = new CitizenInfos(
            new CitizenNumber($citizenNumber),
            clone $handleSC
        );

        $ci->nickname = $nickname;
        if ($mainOrga !== null) {
            $ci->organisations[] = $mainOrga;
        }
        $ci->organisations = array_merge($ci->organisations, $orgaAffiliates);
        $ci->mainOrga = $mainOrga;
        $ci->bio = $bio;
        $ci->avatarUrl = $avatarUrl;
        $ci->registered = $enlisted;

        $this->logger->info('Citizen infos retrieved.', [
            'handle' => $handleSC->getHandle(),
            'citizen_number' => $citizenNumber,
            'infos' => $this->serializer->serialize($ci, 'json'),
        ]);

        return $ci;
    }
}
