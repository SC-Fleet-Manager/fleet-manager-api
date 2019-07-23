<?php

namespace App\Service;

use App\Domain\SpectrumIdentification;
use App\Service\Dto\RsiOrgaMemberInfos;
use Goutte\Client as GoutteClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class FakeOrganizationMembersInfosProvider implements OrganizationMembersInfosProviderInterface
{
    public function retrieveInfos(SpectrumIdentification $sid, bool $cache = true): array
    {
        return ['visibleCitizens' => [
            new RsiOrgaMemberInfos('ionni', 'Ioni', null, 1, 'Soldat'),
            new RsiOrgaMemberInfos('ashuvidz', 'Ashuvidz', null, 5, 'Boss'),
        ], 'countHiddenCitizens' => 3];
    }

    public function getTotalMembers(SpectrumIdentification $sid, bool $cache = true): int
    {
        return 5;
    }
}
