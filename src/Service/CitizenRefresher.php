<?php

namespace App\Service;

use App\Domain\CitizenInfos;
use App\Domain\SpectrumIdentification;
use App\Entity\Citizen;
use App\Entity\CitizenOrganization;
use App\Entity\Organization;
use App\Event\CitizenRefreshedEvent;
use App\Repository\OrganizationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class CitizenRefresher
{
    private $entityManager;
    private $organizationRepository;
    private $organizationInfosProvider;
    private $eventDispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        OrganizationRepository $organizationRepository,
        OrganizationInfosProviderInterface $organizationInfosProvider,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->organizationRepository = $organizationRepository;
        $this->organizationInfosProvider = $organizationInfosProvider;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function refreshCitizen(Citizen $citizen, CitizenInfos $citizenInfos): void
    {
        foreach ($citizenInfos->organizations as $orgaInfo) {
            $orga = $this->organizationRepository->findOneBy(['organizationSid' => $orgaInfo->sid->getSid()]);
            if ($orga === null) {
                $orga = new Organization(Uuid::uuid4());
                $orga->setOrganizationSid($orgaInfo->sid->getSid());
                $this->entityManager->persist($orga);
            }
            $providerOrgaInfos = $this->organizationInfosProvider->retrieveInfos(new SpectrumIdentification($orgaInfo->sid->getSid()));
            $orga->setAvatarUrl($providerOrgaInfos->avatarUrl);
            $orga->setName($providerOrgaInfos->fullname);
        }
        $this->entityManager->flush();

        $this->eventDispatcher->dispatch(new CitizenRefreshedEvent($citizen, $citizenInfos));

        $citizen->setNickname($citizenInfos->nickname);
        $citizen->setBio($citizenInfos->bio);
        $citizen->setAvatarUrl($citizenInfos->avatarUrl);
        $citizen->setLastRefresh(new \DateTimeImmutable());
        $citizen->setRedactedMainOrga($citizenInfos->redactedMainOrga);
        $citizen->setCountRedactedOrganizations($citizenInfos->countRedactedOrganizations);

        // remove left orga
        foreach ($citizen->getOrganizations() as $organization) {
            $sid = $organization->getOrganization()->getOrganizationSid();
            $foundOrgaInfo = null;
            foreach ($citizenInfos->organizations as $orgaInfo) {
                if ($orgaInfo->sid->getSid() === $sid) {
                    $foundOrgaInfo = $orgaInfo;
                    break;
                }
            }
            if ($foundOrgaInfo === null) {
                $citizen->removeOrganization($organization);
                $this->entityManager->remove($organization);
                continue;
            }
        }

        // refresh & join new orga
        foreach ($citizenInfos->organizations as $orgaInfo) {
            $citizenOrga = null;
            foreach ($citizen->getOrganizations() as $organization) {
                if ($orgaInfo->sid->getSid() === $organization->getOrganization()->getOrganizationSid()) {
                    $citizenOrga = $organization;
                    break;
                }
            }
            if ($citizenOrga === null) {
                $citizenOrga = new CitizenOrganization(Uuid::uuid4());
                $citizenOrga->setCitizen($citizen);
                $this->entityManager->persist($citizenOrga);
            }
            $orga = $this->organizationRepository->findOneBy(['organizationSid' => $orgaInfo->sid->getSid()]);
            $citizenOrga->setOrganization($orga);
            $citizenOrga->setOrganizationSid($orgaInfo->sid->getSid());
            $citizenOrga->setRank($orgaInfo->rank);
            $citizenOrga->setRankName($orgaInfo->rankName);

            if ($citizenInfos->mainOrga === $orgaInfo) {
                $citizen->setMainOrga($citizenOrga);
            }
        }
    }
}
