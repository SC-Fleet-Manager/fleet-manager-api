<?php

namespace App\Controller;

use App\Entity\Organization;
use App\Repository\CitizenRepository;
use App\Repository\OrganizationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Cache\CacheInterface;

class SpaController extends AbstractController
{
    public function index(string $spaPath): Response
    {
        return $this->render('base.html.twig');
    }

    public function home(): Response
    {
        return $this->render('home.html.twig');
    }

    public function sitemap(
        SerializerInterface $serializer,
        UrlGeneratorInterface $urlGenerator,
        OrganizationRepository $organizationRepository,
        CitizenRepository $citizenRepository,
        CacheInterface $cache
    ): Response {
        $xmlSitemap = $cache->get('sitemap', function (CacheItem $cacheItem) use ($urlGenerator, $citizenRepository, $organizationRepository, $serializer) {
            $cacheItem->expiresAfter(3600);

            $sitemap = [
                '@xmlns' => 'http://www.sitemaps.org/schemas/sitemap/0.9',
                'url' => [
                    [
                        'loc' => $urlGenerator->generate('spa_home', [], UrlGeneratorInterface::ABSOLUTE_URL),
                    ],
                    [
                        'loc' => $urlGenerator->generate('spa', ['spaPath' => 'privacy-policy'], UrlGeneratorInterface::ABSOLUTE_URL),
                        'changefreq' => 'monthly',
                    ],
                ],
            ];

            $citizens = $citizenRepository->findPublics();
            foreach ($citizens as $citizen) {
                $lastModif = $citizen->getLastRefresh();
                if ($citizen->getLastFleet() !== null && $citizen->getLastFleet()->getUploadDate() > $lastModif) {
                    $lastModif = $citizen->getLastFleet()->getUploadDate();
                }
                $sitemap['url'][] = [
                    'loc' => $urlGenerator->generate('spa', ['spaPath' => "citizen/{$citizen->getActualHandle()->getHandle()}"], UrlGeneratorInterface::ABSOLUTE_URL),
                    'lastmod' => $lastModif->format('c'),
                ];
            }

            /** @var Organization[] $orgas */
            $orgas = $organizationRepository->findBy(['publicChoice' => Organization::PUBLIC_CHOICE_PUBLIC]);
            foreach ($orgas as $orga) {
                $sitemap['url'][] = [
                    'loc' => $urlGenerator->generate('spa', ['spaPath' => "organization-fleet/{$orga->getOrganizationSid()}"], UrlGeneratorInterface::ABSOLUTE_URL),
                ];
            }

            return $serializer->serialize($sitemap, 'xml', ['xml_root_node_name' => 'urlset']);
        });

        return new Response($xmlSitemap, 200, [
            'Content-Type' => 'application/xml',
        ]);
    }
}
