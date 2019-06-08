<?php

namespace App\Service;

use App\Domain\CitizenInfos;
use App\Domain\CitizenOrganizationInfo;
use App\Domain\SpectrumIdentification;
use App\Entity\Citizen;
use App\Entity\CitizenOrganization;
use App\Entity\Organization;
use App\Repository\OrganizationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;

class CitizenRefresher
{
    private $entityManager;
    private $organizationRepository;
    private $organizationInfosProvider;

    public function __construct(
        EntityManagerInterface $entityManager,
        OrganizationRepository $organizationRepository,
        OrganizationInfosProviderInterface $organizationInfosProvider
    ) {
        $this->entityManager = $entityManager;
        $this->organizationRepository = $organizationRepository;
        $this->organizationInfosProvider = $organizationInfosProvider;
    }

    public function refreshCitizen(Citizen $citizen, CitizenInfos $citizenInfos): void
    {
        $citizen->setNickname($citizenInfos->nickname);
        $citizen->setBio($citizenInfos->bio);
        $citizen->setAvatarUrl($citizenInfos->avatarUrl);
        $citizen->setLastRefresh(new \DateTimeImmutable());

        $citizen->setRedactedMainOrga($citizenInfos->redactedMainOrga);
        $citizen->setCountRedactedOrganizations($citizenInfos->countRedactedOrganizations);

        foreach ($citizen->getOrganizations() as $orga) {
            $this->entityManager->remove($orga);
        }

        $citizen->setMainOrga(null);
        $citizen->clearOrganizations();
        foreach ($citizenInfos->organizations as $orgaInfo) {
            $this->refreshOrganization($citizen, $citizenInfos, $orgaInfo);
        }
    }

    private function refreshOrganization(Citizen $citizen, CitizenInfos $citizenInfos, CitizenOrganizationInfo $citizenOrgaInfos): void
    {
        $citizenOrga = new CitizenOrganization(Uuid::uuid4());
        $citizenOrga->setCitizen($citizen);

        $orga = $this->organizationRepository->findOneBy(['organizationSid' => $citizenOrgaInfos->sid->getSid()]);
        if ($orga === null) {
            $orga = new Organization(Uuid::uuid4());
            $orga->setOrganizationSid($citizenOrgaInfos->sid->getSid());
            $this->entityManager->persist($orga);
        }
        $providerOrgaInfos = $this->organizationInfosProvider->retrieveInfos(new SpectrumIdentification($citizenOrgaInfos->sid->getSid()));
        $orga->setAvatarUrl($providerOrgaInfos->avatarUrl);
        $orga->setName($providerOrgaInfos->fullname);

        $citizenOrga->setOrganization($orga);
        $citizenOrga->setOrganizationSid($citizenOrgaInfos->sid->getSid());
        $citizenOrga->setRank($citizenOrgaInfos->rank);
        $citizenOrga->setRankName($citizenOrgaInfos->rankName);
        $citizen->addOrganization($citizenOrga);
        if ($citizenInfos->mainOrga === $citizenOrgaInfos) {
            $citizen->setMainOrga($citizenOrga);
        }
    }
}
