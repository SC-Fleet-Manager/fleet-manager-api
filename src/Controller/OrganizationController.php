<?php

namespace App\Controller;

use App\Domain\SpectrumIdentification;
use App\Entity\Citizen;
use App\Entity\Organization;
use App\Entity\User;
use App\Repository\CitizenRepository;
use App\Repository\OrganizationRepository;
use App\Repository\ShipRepository;
use App\Service\FleetOrganizationGuard;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;

/**
 * @Route("/api", name="api_organization_")
 */
class OrganizationController extends AbstractController
{
    private $security;
    private $citizenRepository;
    private $organizationRepository;
    private $shipRepository;
    private $entityManager;
    private $fleetOrganizationGuard;

    public function __construct(
        Security $security,
        CitizenRepository $citizenRepository,
        OrganizationRepository $organizationRepository,
        ShipRepository $shipRepository,
        EntityManagerInterface $entityManager,
        FleetOrganizationGuard $fleetOrganizationGuard
    ) {
        $this->security = $security;
        $this->citizenRepository = $citizenRepository;
        $this->organizationRepository = $organizationRepository;
        $this->shipRepository = $shipRepository;
        $this->entityManager = $entityManager;
        $this->fleetOrganizationGuard = $fleetOrganizationGuard;
    }

    /**
     * @Route("/organization/{organizationSid}/save-preferences", name="save_preferences", methods={"POST"}, condition="request.getContentType() == 'json'")
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED"))
     */
    public function savePreferences(Request $request, string $organizationSid): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $content = \json_decode($request->getContent(), true);

        $citizen = $user->getCitizen();
        if ($citizen === null) {
            return $this->json([
                'error' => 'no_citizen_created',
                'errorMessage' => 'Your RSI account must be linked first. Go to the <a href="/profile">profile page</a>.',
            ], 400);
        }
        /** @var Organization|null $organization */
        $organization = $this->organizationRepository->findOneBy(['organizationSid' => $organizationSid]);
        if ($organization === null) {
            return $this->json([
                'error' => 'not_found_orga',
                'errorMessage' => sprintf('The organization "%s" does not exist.', $organizationSid),
            ], 404);
        }

        $admins = $this->citizenRepository->findAdminByOrganization($organizationSid);
        $isAdmin = false;
        foreach ($admins as $admin) {
            if ($admin->getId()->equals($citizen->getId())) {
                $isAdmin = true;
                break;
            }
        }
        if (!$isAdmin) {
            return $this->json([
                'error' => 'not_enough_rights',
                'errorMessage' => sprintf('You must be an admin of %s to change their settings. Try to refresh your RSI profile in your <a href="/profile">profile page</a>.', $organization->getName()),
            ], 403);
        }

        if (!isset($content['publicChoice'])) {
            return $this->json([
                'error' => 'invalid_form',
                'errorMessage' => 'The field publicChoice must not be blank.',
            ], 400);
        }

        $organization->setPublicChoice($content['publicChoice']);
        $this->entityManager->flush();

        return $this->json(null, 204);
    }

    /**
     * @Route("/organization/{organizationSid}/citizens", name="citizens", methods={"GET"})
     */
    public function citizens(string $organizationSid): Response
    {
        if (null !== $response = $this->fleetOrganizationGuard->checkAccessibleOrganization($organizationSid)) {
            return $response;
        }
        $citizen = null;
        if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            /** @var User $user */
            $user = $this->security->getUser();
            $citizen = $user->getCitizen();
        }

        $citizens = $this->citizenRepository->findVisiblesByOrganization($organizationSid, $citizen);

        // format for vue-select data
        $res = array_map(static function (Citizen $citizen) {
            return [
                'id' => $citizen->getId(),
                'label' => $citizen->getActualHandle()->getHandle(),
            ];
        }, $citizens);

        $collator = \Collator::create(\Locale::getDefault());
        usort($res, static function (array $item1, array $item2) use ($collator): int {
            return $collator->compare($item1['label'], $item2['label']);
        });

        return $this->json($res);
    }

    /**
     * @Route("/organization/{organizationSid}/ships", name="ships", methods={"GET"})
     */
    public function ships(string $organizationSid): Response
    {
        if (null !== $response = $this->fleetOrganizationGuard->checkAccessibleOrganization($organizationSid)) {
            return $response;
        }

        $ships = $this->shipRepository->getFiltrableOrganizationShipNames(new SpectrumIdentification($organizationSid));

        $res = array_map(static function (array $ship) {
            return [
                'id' => $ship['shipName'],
                'label' => $ship['shipName'],
            ];
        }, $ships);

        return $this->json($res);
    }
}
