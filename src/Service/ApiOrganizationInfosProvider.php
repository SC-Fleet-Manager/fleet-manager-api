<?php

namespace App\Service;

use App\Domain\OrganizationInfos;
use App\Domain\SpectrumIdentification;
use Goutte\Client as GoutteClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Contracts\Cache\CacheInterface;

class ApiOrganizationInfosProvider implements OrganizationInfosProviderInterface
{
    private const BASE_URL = 'https://robertsspaceindustries.com';

    /** @var GoutteClient */
    private $client;
    private $logger;
    private $cache;

    public function __construct(LoggerInterface $logger, CacheInterface $cache)
    {
        $this->client = new GoutteClient();
        $this->logger = $logger;
        $this->cache = $cache;
    }

    public function retrieveInfos(SpectrumIdentification $sid): OrganizationInfos
    {
        return $this->cache->get('organization_info_'.$sid->getSid(), function (CacheItem $cacheItem) use ($sid) {
            $cacheItem->tag(['organization_infos']);
            $cacheItem->expiresAfter(new \DateInterval('P1D'));

            return $this->scrap($sid);
        });
    }

    private function scrap(SpectrumIdentification $sid): OrganizationInfos
    {
        $crawler = $this->client->request('GET', self::BASE_URL.'/orgs/'.$sid);
        $profileCrawler = $crawler->filter('#organization');

        $avatarUrl = null;
        $avatarCrawler = $profileCrawler->filter('.heading .logo img');
        if ($avatarCrawler->count() > 0) {
            $avatarUrl = self::BASE_URL.$avatarCrawler->attr('src');
        }

        $fullname = $sid->getSid();
        $fullnameCrawler = $profileCrawler->filter('.heading h1');
        if ($fullnameCrawler->count() > 0) {
            $text = trim($fullnameCrawler->getNode(0)->firstChild->wholeText);
            $text = trim(substr($text, 0, -1));
            $fullname = $text;
        }

        $oi = new OrganizationInfos($fullname, clone $sid, $avatarUrl);

        $this->logger->info('Organization infos retrieved.', [
            'sid' => (string) $sid,
        ]);

        return $oi;
    }
}
