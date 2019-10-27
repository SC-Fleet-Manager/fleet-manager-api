<?php

namespace App\Controller\Spa;

use App\Entity\Organization;
use App\Repository\CitizenRepository;
use App\Repository\OrganizationRepository;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class SitemapController
{
    private $serializer;
    private $urlGenerator;
    private $organizationRepository;
    private $citizenRepository;
    private $cache;

    public function __construct(
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        OrganizationRepository $organizationRepository,
        CitizenRepository $citizenRepository,
        CacheInterface $cache
    ) {
        $this->serializer = $serializer;
        $this->urlGenerator = $urlGenerator;
        $this->organizationRepository = $organizationRepository;
        $this->citizenRepository = $citizenRepository;
        $this->cache = $cache;
    }

    /**
     * @Route("/sitemap.xml", name="spa_sitemap", methods={"GET"})
     */
    public function __invoke(): Response
    {
        $xmlSitemap = $this->cache->get('sitemap', function (CacheItem $cacheItem) {
            $cacheItem->expiresAfter(3600);

            $sitemap = [
                '@xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
                'url' => [
                    [
                        'loc' => $this->urlGenerator->generate('spa_home', [], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                    [
                        'loc' => $this->urlGenerator->generate('spa', ['spaPath' => 'privacy-policy'], UrlGeneratorInterface::ABSOLUTE_URL),
                        'changefreq' => 'monthly',
                    ],
                ],
            ];

            $citizens = $this->citizenRepository->findPublics();
            foreach ($citizens as $citizen) {
                $lastModif = $citizen->getLastRefresh();
                if ($citizen->getLastFleet() !== null && $citizen->getLastFleet()->getUploadDate() > $lastModif) {
                    $lastModif = $citizen->getLastFleet()->getUploadDate();
                }
                $sitemap['url'][] = [
                    'loc' => $this->urlGenerator->generate('spa', ['spaPath' => "citizen/{$citizen->getActualHandle()->getHandle()}"], UrlGeneratorInterface::ABSOLUTE_URL),
                    'lastmod' => $lastModif->format('c'),
                ];
            }

            /** @var Organization[] $orgas */
            $orgas = $this->organizationRepository->findBy(['publicChoice' => Organization::PUBLIC_CHOICE_PUBLIC]);
            foreach ($orgas as $orga) {
                $sitemap['url'][] = [
                    'loc' => $this->urlGenerator->generate('spa', ['spaPath' => "organization-fleet/{$orga->getOrganizationSid()}"], UrlGeneratorInterface::ABSOLUTE_URL),
                ];
            }

            return $this->serializer->serialize($sitemap, 'xml', ['xml_root_node_name' => 'urlset']);
        });

        return new Response($xmlSitemap, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
