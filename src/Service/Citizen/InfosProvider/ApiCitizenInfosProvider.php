<?php

namespace App\Service\Citizen\InfosProvider;

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

    /**
     * @throws NotFoundHandleSCException
     */
    public function retrieveInfos(HandleSC $handleSC, bool $caching = true): CitizenInfos
    {
        return $this->cache->get('citizen_info_'.sha1($handleSC->getHandle()), function (CacheItem $cacheItem) use ($handleSC) {
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
            throw new NotFoundHandleSCException($handleSC);
        }

        $nickname = null;
        $nicknameCrawler = $profileCrawler->filter('.profile .info .entry:first-child .value');
        if ($nicknameCrawler->count() > 0) {
            $nickname = trim($nicknameCrawler->text());
        }

        $avatarUrl = null;
        $avatarCrawler = $profileCrawler->filter('.profile .thumb img');
        if ($avatarCrawler->count() > 0 && $avatarCrawler->attr('src') !== '') {
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
        $mainOrgaRedacted = false;
        $mainOrgaCrawler = $crawler->filter('.org.main.visibility-V');
        if ($mainOrgaCrawler->count() > 0) {
            $sid = $mainOrgaCrawler->filterXPath('//p[contains(.//*/text(), "Spectrum Identification (SID)")]/*[contains(@class, "value")]')->text();
            $rankName = $mainOrgaCrawler->filterXPath('//p[contains(.//*/text(), "Organization rank")]/*[contains(@class, "value")]')->text();
            $rank = $mainOrgaCrawler->filter('.ranking .active')->count();

            $mainOrga = new CitizenOrganizationInfo(new SpectrumIdentification($sid), $rank, $rankName);
        } elseif ($crawler->filter('.org.main.visibility-R')->count() > 0) {
            $mainOrgaRedacted = true;
        }

        $orgaAffiliates = [];
        $crawler->filter('.org.affiliation.visibility-V')->each(static function (Crawler $node) use (&$orgaAffiliates) {
            $sid = $node->filterXPath('//p[contains(.//*/text(), "Spectrum Identification (SID)")]/*[contains(@class, "value")]')->text();
            $rankName = $node->filterXPath('//p[contains(.//*/text(), "Organization rank")]/*[contains(@class, "value")]')->text();
            $rank = $node->filter('.ranking .active')->count();

            $orga = new CitizenOrganizationInfo(new SpectrumIdentification($sid), $rank, $rankName);
            $orgaAffiliates[] = $orga;
        });
        $redactedAffiliates = $crawler->filter('.org.affiliation.visibility-R')->count();

        $ci = new CitizenInfos(
            new CitizenNumber($citizenNumber),
            clone $handleSC
        );

        $ci->nickname = $nickname;
        if ($mainOrga !== null) {
            $ci->organizations[] = $mainOrga;
        }
        $ci->organizations = array_merge($ci->organizations, $orgaAffiliates);
        $ci->mainOrga = $mainOrga;
        $ci->bio = $bio;
        $ci->avatarUrl = $avatarUrl;
        $ci->registered = $enlisted;
        $ci->redactedMainOrga = $mainOrgaRedacted;
        $ci->countRedactedOrganizations = $redactedAffiliates + ($mainOrgaRedacted ? 1 : 0);

        $this->logger->info('Citizen infos retrieved.', [
            'handle' => $handleSC->getHandle(),
            'citizen_number' => $citizenNumber,
            'infos' => $this->serializer->serialize($ci, 'json'),
        ]);

        return $ci;
    }
}
