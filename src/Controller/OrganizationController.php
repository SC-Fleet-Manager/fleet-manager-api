<?php

namespace App\Controller;

use App\Domain\SpectrumIdentification;
use App\Entity\Citizen;
use App\Entity\Organization;
use App\Entity\User;
use App\Repository\CitizenRepository;
use App\Repository\OrganizationRepository;
use App\Repository\ShipRepository;
use App\Service\Dto\RsiOrgaMemberInfos;
use App\Service\FleetOrganizationGuard;
use App\Service\OrganizationMembersInfosProviderInterface;
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
     * @Route("/organization/{organizationSid}/members-registered", name="members_registered", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function membersRegistered(Request $request, string $organizationSid, OrganizationMembersInfosProviderInterface $organizationMembersInfosProvider): Response
    {
        // TODO : [optimization] pagination
//        $page = $request->query->getInt('page', 1);
//        $page = $page >= 1 ? $page : 1;
//        $itemsPerPage = 50;

        /** @var User $user */
        $user = $this->security->getUser();
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

        if (!$this->isAdminOf($citizen, $organizationSid)) {
            return $this->json([
                'error' => 'not_enough_rights',
                'errorMessage' => sprintf('You must be an admin of %s to view these stats. Try to refresh your RSI profile in your <a href="/profile">profile page</a>.', $organization->getName()),
            ], 403);
        }

        $memberInfos = $organizationMembersInfosProvider->retrieveInfos(new SpectrumIdentification($organizationSid));
        $countVisibleCitizens = count($memberInfos['visibleCitizens']);
        // pagination
//        $memberInfos['visibleCitizens'] = array_splice($memberInfos['visibleCitizens'], ($page - 1) * $itemsPerPage, $itemsPerPage);

        $handles = array_map(static function (RsiOrgaMemberInfos $info) {
            return mb_strtolower($info->handle);
        }, $memberInfos['visibleCitizens']);
        $citizens = $this->citizenRepository->findSomeHandlesWithLastFleet($handles);

        $members = [];
        /** @var RsiOrgaMemberInfos $memberInfo */
        foreach ($memberInfos['visibleCitizens'] as $memberInfo) {
            $memberCitizen = null;
            foreach ($citizens as $citizen) {
                if (mb_strtolower($citizen->getActualHandle()->getHandle()) === mb_strtolower($memberInfo->handle)) {
                    $memberCitizen = $citizen;
                    break;
                }
            }
            if ($memberCitizen === null) {
                $members[] = [
                    'infos' => $memberInfo,
                    'status' => RsiOrgaMemberInfos::STATUS_NOT_REGISTERED,
                ];
            } elseif ($memberCitizen->getLastFleet() === null) {
                $members[] = [
                    'infos' => $memberInfo,
                    'status' => RsiOrgaMemberInfos::STATUS_REGISTERED,
                ];
            } else {
                $members[] = [
                    'lastFleetUploadDate' => $memberCitizen->getLastFleet()->getUploadDate()->format('Y-m-d'),
                    'infos' => $memberInfo,
                    'status' => RsiOrgaMemberInfos::STATUS_FLEET_UPLOADED,
                ];
            }
        }

        // sorting
        $collator = \Collator::create(\Locale::getDefault());
        usort($members, static function (array $member1, array $member2) use ($collator) {
            $rankCmp = $member2['infos']->rank - $member1['infos']->rank;
            if ($rankCmp !== 0) {
                return $rankCmp;
            }
            if ($member1['status'] !== $member2['status']) {
                if ($member1['status'] === RsiOrgaMemberInfos::STATUS_FLEET_UPLOADED) {
                    return -1;
                }
                if ($member2['status'] === RsiOrgaMemberInfos::STATUS_FLEET_UPLOADED) {
                    return 1;
                }
                if ($member1['status'] === RsiOrgaMemberInfos::STATUS_REGISTERED) {
                    return -1;
                }
                if ($member2['status'] === RsiOrgaMemberInfos::STATUS_REGISTERED) {
                    return 1;
                }
                if ($member1['status'] === RsiOrgaMemberInfos::STATUS_NOT_REGISTERED) {
                    return -1;
                }

                return 1;
            }

            return $collator->compare($member1['infos']->nickname, $member2['infos']->nickname);
        });

        return $this->json([
//            'page' => $page,
//            'totalPage' => ceil($countVisibleCitizens / $itemsPerPage),
            'totalItems' => $countVisibleCitizens,
            'members' => $members,
            'countHiddenMembers' => $memberInfos['countHiddenCitizens'],
        ]);
    }

    private function isAdminOf(Citizen $citizen, string $organizationSid): bool
    {
        $admins = $this->citizenRepository->findAdminByOrganization($organizationSid);
        foreach ($admins as $admin) {
            if ($admin->getId()->equals($citizen->getId())) {
                return true;
            }
        }

        return false;
    }

    /**
     * @Route("/organization/{organizationSid}/save-preferences", name="save_preferences", methods={"POST"}, condition="request.getContentType() == 'json'")
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
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

        if (!$this->isAdminOf($citizen, $organizationSid)) {
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

        // orga public + not in this orga ? we can't filter by citizens
        if ($citizen !== null) {
            if (!$citizen->hasOrganization($organizationSid)) {
                return $this->json([]);
            }
        } else {
            return $this->json([]);
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
