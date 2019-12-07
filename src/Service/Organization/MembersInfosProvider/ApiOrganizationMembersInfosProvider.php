<?php

namespace App\Service\Organization\MembersInfosProvider;

use App\Domain\SpectrumIdentification;
use App\Service\Dto\RsiOrgaMemberInfos;
use Goutte\Client as GoutteClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ApiOrganizationMembersInfosProvider implements OrganizationMembersInfosProviderInterface
{
    private const BASE_URL = 'https://robertsspaceindustries.com';

    private $client;
    private $httpClient;
    private $logger;
    private $cache;

    public function __construct(LoggerInterface $logger, CacheInterface $cache, HttpClientInterface $rsiOrgaMembersClient)
    {
        $this->client = new GoutteClient();
        $this->httpClient = $rsiOrgaMembersClient;
        $this->logger = $logger;
        $this->cache = $cache;
    }

    /**
     * @return array ['visibleCitizens' => <RsiOrgaMemberInfos[]>, 'countHiddenCitizens' => <int>]
     */
    public function retrieveInfos(SpectrumIdentification $sid, bool $cache = true): array
    {
        return $this->cache->get('organization_members_infos_'.$sid->getSid(), function (CacheItem $cacheItem) use ($sid) {
            $cacheItem->tag(['organization_members_infos']);
            $cacheItem->expiresAfter(new \DateInterval('P1D'));

            return $this->scrap($sid);
        }, $cache ? null : INF);
    }

    public function getTotalMembers(SpectrumIdentification $sid, bool $cache = true): int
    {
        return $this->cache->get('organization_members_count_'.$sid->getSid(), function (CacheItem $cacheItem) use ($sid) {
            $cacheItem->tag(['organization_members_count']);
            $cacheItem->expiresAfter(new \DateInterval('P1D'));

            $crawler = $this->client->request('GET', self::BASE_URL.'/orgs/'.$sid.'/members');
            $countCrawler = $crawler->filter('#organization .logo .count');
            if ($countCrawler->count() === 0) {
                throw new \LogicException('No data to scrap.');
            }
            preg_match('~^(?P<count>\d+)~', $countCrawler->text(null, true), $matches);

            return (int) $matches['count'];
        }, $cache ? null : INF);
    }

    private function scrap(SpectrumIdentification $sid): ?array
    {
        $totalMembers = $this->getTotalMembers($sid, false);
        if ($totalMembers > 128) {
            return [
                'error' => 'orga_too_big',
            ];
        }

        /** @var ResponseInterface[] $responses */
        $responses = [];
        for ($page = 1, $totalPages = ceil($totalMembers / 32); $page <= $totalPages; ++$page) {
            $responses[] = $this->httpClient->request('POST', '/api/orgs/getOrgMembers', [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => [
                    'symbol' => $sid->getSid(),
                    'search' => '',
                    'pagesize' => 32,
                    'page' => $page,
                ],
            ]);
        }

        $visibleCitizens = [];
        $hiddenCitizens = 0;
        foreach ($responses as $response) {
            $content = $response->toArray();
            if (!$content['success']) {
                $this->logger->warning('[ApiOrgaMembersInfosProvider] no success response from RSI.', ['sid' => $sid->getSid(), 'content' => $content, 'headers' => $response->getHeaders()]);
                throw new \LogicException('No success response from provider.'); // all or nothing
            }

            $crawler = new Crawler($content['data']['html']);
            $hiddenCitizens += $crawler->filter('.member-item.org-visibility-R')->count();
            $hiddenCitizens += $crawler->filter('.member-item.org-visibility-H')->count();
            $crawler->filter('.member-item.org-visibility-V')->each(static function (Crawler $crawler) use (&$visibleCitizens) {
                $nickname = $crawler->filter('.name-wrap .name')->text(null, true);
                $handle = $crawler->filter('.name-wrap .nick')->text(null, true);
                $avatarUrl = null;
                if ($crawler->filter('.thumb img')->attr('src') !== '') {
                    $avatarUrl = self::BASE_URL.$crawler->filter('.thumb img')->attr('src');
                }
                $rankName = $crawler->filter('.frontinfo .rank')->text(null, true);
                $rankStyle = $crawler->filter('.frontinfo .ranking-stars .stars')->attr('style');
                $rank = 0;
                switch ($rankStyle) {
                    case 'width: 0%;':
                        $rank = 0;
                        break;
                    case 'width: 20%;':
                        $rank = 1;
                        break;
                    case 'width: 40%;':
                        $rank = 2;
                        break;
                    case 'width: 60%;':
                        $rank = 3;
                        break;
                    case 'width: 80%;':
                        $rank = 4;
                        break;
                    case 'width: 100%;':
                        $rank = 5;
                        break;
                }

                $visibleCitizens[] = new RsiOrgaMemberInfos($handle, $nickname, $avatarUrl, $rank, $rankName);
            });
        }

        return [
            'visibleCitizens' => $visibleCitizens,
            'countHiddenCitizens' => $hiddenCitizens,
        ];
    }
}
