<?php

namespace App\Controller;

use App\Domain\SpectrumIdentification;
use App\Entity\Citizen;
use App\Entity\Organization;
use App\Entity\User;
use App\Repository\CitizenRepository;
use App\Repository\OrganizationRepository;
use App\Repository\ShipRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * @Route("/api", name="api_organization_")
 */
class OrganizationController extends AbstractController
{
    private $security;
    private $citizenRepository;
    private $organizationRepository;
    private $shipRepository;
    private $serializer;

    public function __construct(
        Security $security,
        CitizenRepository $citizenRepository,
        OrganizationRepository $organizationRepository,
        ShipRepository $shipRepository,
        SerializerInterface $serializer
    ) {
        $this->security = $security;
        $this->citizenRepository = $citizenRepository;
        $this->organizationRepository = $organizationRepository;
        $this->shipRepository = $shipRepository;
        $this->serializer = $serializer;
    }

    /**
     * @Route("/organization/{organizationSid}/citizens", name="citizens", methods={"GET"})
     */
    public function citizens(string $organizationSid): Response
    {
        $citizen = null;
        if ($this->security->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            /** @var User $user */
            $user = $this->security->getUser();
            $citizen = $user->getCitizen();
        }
        if (!$this->isPublicOrga($organizationSid)) {
            $this->denyAccessUnlessGranted('IS_AUTHENTICATED_REMEMBERED');

            if ($citizen === null) {
                return $this->json([
                    'error' => 'no_citizen_created',
                    'errorMessage' => 'Your RSI account must be linked first. Go to the <a href="/profile">profile page</a>.',
                ], 400);
            }
            if (!$citizen->hasOrganization($organizationSid)) {
                return $this->json([
                    'error' => 'bad_organization',
                    'errorMessage' => sprintf('The organization %s does not exist.', $organizationSid),
                ], 404);
            }
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
            if (!$citizen->hasOrganization($organizationSid)) {
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
                'label' => $ship['shipName'],
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

    /**
     * @Route("/export-orga-fleet/{organization}", name="export_orga_fleet", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function exportOrgaFleet(string $organization): Response
    {
        /** @var User $user */
        $user = $this->security->getUser();
        $citizen = $user->getCitizen();
        if ($citizen === null) {
            throw $this->createNotFoundException(sprintf('The user "%s" has no citizens.', $user->getId()));
        }
        if (!$citizen->hasOrganization($organization)) {
            throw $this->createNotFoundException(sprintf('The citizen "%s" does not have the organization "%s".', $citizen->getId(), $organization));
        }

        $citizens = $this->citizenRepository->getByOrganization(new SpectrumIdentification($organization));

        $ships = [];
        $totalColumn = [];
        foreach ($citizens as $citizen) {
            $citizenHandle = $citizen->getActualHandle()->getHandle();
            $lastFleet = $citizen->getLastFleet();
            if ($lastFleet === null) {
                continue;
            }
            foreach ($lastFleet->getShips() as $ship) {
                if (!isset($ships[$ship->getName()])) {
                    $ships[$ship->getName()] = [$citizenHandle => 1];
                } elseif (!isset($ships[$ship->getName()][$citizenHandle])) {
                    $ships[$ship->getName()][$citizenHandle] = 1;
                } else {
                    ++$ships[$ship->getName()][$citizenHandle];
                }
            }
        }
        ksort($ships);

        $data = [];
        foreach ($ships as $shipName => $owners) {
            $total = 0;
            $columns = [];
            foreach ($owners as $ownerName => $countOwner) {
                $total += $countOwner;
                $columns[$ownerName] = $countOwner;
                if (!isset($totalColumn[$ownerName])) {
                    $totalColumn[$ownerName] = $countOwner;
                } else {
                    $totalColumn[$ownerName] += $countOwner;
                }
            }
            $data[] = array_merge([
                'Ship Model' => $shipName,
                'Ship Total' => $total,
            ], $columns);
        }

        $total = 0;
        $columns = [];
        foreach ($totalColumn as $ownerName => $countOwner) {
            $total += $countOwner;
            $columns[$ownerName] = $countOwner;
        }
        $data[] = array_merge([
            'Ship Model' => 'Total',
            'Ship Total' => $total,
        ], $columns);

        $csv = $this->serializer->serialize($data, 'csv');
        $filepath = sys_get_temp_dir().'/'.uniqid('', true);
        file_put_contents($filepath, $csv);

        $file = $this->file($filepath, 'export_'.$organization.'.csv');
        $file->headers->set('Content-Type', 'application/csv');
        $file->deleteFileAfterSend();

        return $file;
    }
}
