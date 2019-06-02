<?php

namespace App\Controller;

use App\Domain\SpectrumIdentification;
use App\Entity\Citizen;
use App\Entity\Organization;
use App\Entity\User;
use App\Repository\CitizenRepository;
use App\Repository\OrganizationRepository;
use App\Repository\ShipRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/api/organization", name="organization_")
 */
class OrganizationController extends AbstractController
{
    private $security;
    private $citizenRepository;
    private $organizationRepository;
    private $shipRepository;

    public function __construct(
        Security $security,
        CitizenRepository $citizenRepository,
        OrganizationRepository $organizationRepository,
        ShipRepository $shipRepository
    ) {
        $this->security = $security;
        $this->citizenRepository = $citizenRepository;
        $this->organizationRepository = $organizationRepository;
        $this->shipRepository = $shipRepository;
    }

    /**
     * @Route("/{organizationSid}/citizens", name="citizens", methods={"GET"})
     */
    public function citizens(string $organizationSid): Response
    {
        if (!$this->isPublicOrga($organizationSid)) {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

            /** @var User $user */
            $user = $this->security->getUser();
            $citizen = $user->getCitizen();
            if ($citizen === null) {
                return $this->json([
                    'error' => 'no_citizen_created',
                    'errorMessage' => 'Your RSI account must be linked first. Go to the <a href="/profile">profile page</a>.',
                ], 400);
            }
            if (!$citizen->hasOrganisation($organizationSid)) {
                return $this->json([
                    'error' => 'bad_organization',
                    'errorMessage' => sprintf('The organization %s does not exist.', $organizationSid),
                ], 404);
            }
        }

        $citizens = $this->citizenRepository->getByOrganisation(new SpectrumIdentification($organizationSid));

        $res = array_map(static function (Citizen $citizen) {
            return [
                'id' => $citizen->getId(),
                'text' => $citizen->getActualHandle()->getHandle(),
            ];
        }, $citizens);

        $collator = \Collator::create(\Locale::getDefault());
        usort($res, static function (array $item1, array $item2) use ($collator): int {
            return $collator->compare($item1['text'], $item2['text']);
        });

        return $this->json($res);
    }

    /**
     * @Route("/{organizationSid}/ships", name="ships", methods={"GET"})
     */
    public function ships(string $organizationSid): Response
    {
        if (!$this->isPublicOrga($organizationSid)) {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

            /** @var User $user */
            $user = $this->security->getUser();
            $citizen = $user->getCitizen();
            if ($citizen === null) {
                return $this->json([
                    'error' => 'no_citizen_created',
                    'errorMessage' => 'Your RSI account must be linked first. Go to the <a href="/profile">profile page</a>.',
                ], 400);
            }
            if (!$citizen->hasOrganisation($organizationSid)) {
                return $this->json([
                    'error' => 'bad_organization',
                    'errorMessage' => sprintf('The organization %s does not exist.', $organizationSid),
                ], 404);
            }
        }

        $ships = $this->shipRepository->getFiltrableOrganizationShipNames(new SpectrumIdentification($organizationSid));

        $res = array_map(static function (array $ship) {
            return [
                'id' => $ship['shipName'],
                'text' => $ship['shipName'],
            ];
        }, $ships);

        return $this->json($res);
    }

    private function isPublicOrga(string $organizationSid): bool
    {
        /** @var Organization $orga */
        $orga = $this->organizationRepository->findOneBy(['organizationSid' => $organizationSid]);
        if ($orga === null) {
            return false;
        }

        return $orga->getPublicChoice() === Organization::PUBLIC_CHOICE_PUBLIC;
    }
}
