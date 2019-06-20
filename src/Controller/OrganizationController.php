<?php

namespace App\Controller;

use App\Domain\SpectrumIdentification;
use App\Entity\Citizen;
use App\Entity\Organization;
use App\Entity\OrganizationChange;
use App\Entity\User;
use App\Repository\CitizenRepository;
use App\Repository\OrganizationRepository;
use App\Repository\ShipRepository;
use App\Service\CitizenInfosProviderInterface;
use App\Service\CitizenRefresher;
use App\Service\Dto\RsiOrgaMemberInfos;
use App\Service\Exporter\OrganizationFleetExporter;
use App\Service\FleetOrganizationGuard;
use App\Service\OrganizationMembersInfosProviderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
     * @Route("/organization/{organizationSid}/changes", name="changes", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function changes(string $organizationSid): Response
    {
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

        $changes = $this->entityManager->getRepository(OrganizationChange::class)->findBy(['organization' => $organization], ['createdAt' => 'DESC'], 50);

        return $this->json($changes, 200, [], ['groups' => 'orga_fleet_admin']);
    }

    /**
     * @Route("/organization/export-orga-members/{organizationSid}", name="export_orga_members", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function exportOrgaMembers(string $organizationSid, OrganizationFleetExporter $orgaFleetExporter, SerializerInterface $serializer): Response
    {
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

        try {
            $data = $orgaFleetExporter->exportOrgaMembers($organizationSid);
        } catch (\LogicException $e) {
            return $this->json([
                'error' => 'orga_too_big',
                'errorMessage' => 'Sorry, your orga is too big to retrieve the members list right now. We\'re currently searching a solution for this issue.',
            ], 400);
        }

        $csv = $serializer->serialize($data, 'csv');
        $filepath = sys_get_temp_dir().'/'.uniqid('', true);
        file_put_contents($filepath, $csv);

        $file = $this->file($filepath, 'export_'.$organizationSid.'.csv');
        $file->headers->set('Content-Type', 'application/csv');
        $file->deleteFileAfterSend();

        return $file;
    }

    /**
     * @Route("/organization/{organizationSid}/refresh-member/{handle}", name="refresh_member", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function refreshMember(string $organizationSid, string $handle, CitizenInfosProviderInterface $citizenInfosProvider, CitizenRefresher $citizenRefresher): Response
    {
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

        /** @var Citizen|null $targetCitizen */
        $targetCitizen = $this->citizenRepository->findOneBy(['actualHandle' => $handle]);
        if ($targetCitizen === null) {
            return $this->json([
                'error' => 'not_found_citizen',
                'errorMessage' => sprintf('The citizen "%s" does not exist.', $handle),
            ], 404);
        }

        $citizenInfos = $citizenInfosProvider->retrieveInfos(clone $targetCitizen->getActualHandle(), false);
        if (!$citizenInfos->numberSC->equals($targetCitizen->getNumber())) {
            return $this->json([
                'error' => 'bad_citizen',
                'errorMessage' => sprintf('The SC handle of %s has probably changed. He should update it in its Profile.', $targetCitizen->getActualHandle()->getHandle()),
            ], 400);
        }

        $citizenRefresher->refreshCitizen($targetCitizen, $citizenInfos);
        $this->entityManager->flush();

        return $this->json(null, 204);
    }

    /**
     * @Route("/organization/{organizationSid}/refresh-orga", name="refresh_orga", methods={"POST"})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function refreshOrga(string $organizationSid, OrganizationMembersInfosProviderInterface $organizationMembersInfosProvider): Response
    {
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

        if (!$organization->canBeRefreshed()) {
            return $this->json([
                'error' => 'too_many_refresh',
                'errorMessage' => sprintf('Sorry, you have to wait %s minutes before refreshing.', $organization->getTimeLeftBeforeRefreshing()->format('%i')),
            ], 400);
        }

        $memberInfos = $organizationMembersInfosProvider->retrieveInfos(new SpectrumIdentification($organizationSid), false);
        if (isset($memberInfos['error']) && $memberInfos['error'] === 'orga_too_big') {
            return $this->json([
                'error' => 'orga_too_big',
                'errorMessage' => 'Sorry, your orga is too big to retrieve the members list right now. We\'re currently searching a solution for this issue.',
            ], 400);
        }
        $organization->setLastRefresh(new \DateTimeImmutable());
        $this->entityManager->flush();

        return $this->json($this->prepareResponseMembersList($memberInfos));
    }

    private function prepareResponseMembersList(array $memberInfos): array
    {
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

            return $collator->compare($member1['infos']->handle, $member2['infos']->handle);
        });

        return [
//            'page' => $page,
//            'totalPage' => ceil($countVisibleCitizens / $itemsPerPage),
            'totalItems' => $countVisibleCitizens,
            'members' => $members,
            'countHiddenMembers' => $memberInfos['countHiddenCitizens'],
        ];
    }

    /**
     * @Route("/organization/{organizationSid}/members-registered", name="members_registered", methods={"GET"})
     * @IsGranted("IS_AUTHENTICATED_REMEMBERED")
     */
    public function membersRegistered(string $organizationSid, OrganizationMembersInfosProviderInterface $organizationMembersInfosProvider): Response
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
        if (isset($memberInfos['error']) && $memberInfos['error'] === 'orga_too_big') {
            return $this->json([
                'error' => 'orga_too_big',
                'errorMessage' => 'Sorry, your orga is too big to retrieve the members list right now. We\'re currently searching a solution for this issue.',
            ], 400);
        }

        return $this->json($this->prepareResponseMembersList($memberInfos));
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
