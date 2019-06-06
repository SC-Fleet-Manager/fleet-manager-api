<?php

namespace App\Service;

use App\Domain\SpectrumIdentification;
use App\Entity\CitizenOrganization;
use App\Entity\Organization;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;

class OrganizationCreator
{
    private $entityManager;
    private $orgaInfosProvider;

    public function __construct(EntityManagerInterface $entityManager, OrganizationInfosProviderInterface $orgaInfosProvider)
    {
        $this->entityManager = $entityManager;
        $this->orgaInfosProvider = $orgaInfosProvider;
    }

    /**
     * Creates an Organization for each not existing given orgas.
     *
     * @param CitizenOrganization[] $organizations
     */
    public function createAndUpdateOrganizations(array $organizations): void
    {
        $sids = array_map(static function (CitizenOrganization $orga) {
            return $orga->getOrganizationSid();
        }, $organizations);

        /** @var Organization[] $existingOrgas */
        $existingOrgas = $this->entityManager->getRepository(Organization::class)->findBy(['organizationSid' => $sids]);
        foreach ($organizations as $orga) {
            $organization = null;
            foreach ($existingOrgas as $existingOrga) {
                if ($existingOrga->getOrganizationSid() === $orga->getOrganizationSid()) {
                    $organization = $existingOrga;
                    break;
                }
            }
            if ($organization === null) {
                $organization = new Organization(Uuid::uuid4());
                $organization->setOrganizationSid($orga->getOrganizationSid());
                $this->entityManager->persist($organization);
            }
            $providerOrgaInfos = $this->orgaInfosProvider->retrieveInfos(new SpectrumIdentification($orga->getOrganizationSid()));
            $organization->setAvatarUrl($providerOrgaInfos->avatarUrl);
            $organization->setName($providerOrgaInfos->fullname);
        }

        $this->entityManager->flush();
    }

    /**
     * @param string[] $organizationSids
     */
    public function createAndUpdateOrganizationsFromSids(array $organizationSids): void
    {
        /** @var Organization[] $existingOrgas */
        $existingOrgas = $this->entityManager->getRepository(Organization::class)->findBy(['organizationSid' => $organizationSids]);
        foreach ($organizationSids as $orgaSid) {
            $organization = null;
            foreach ($existingOrgas as $existingOrga) {
                if ($existingOrga->getOrganizationSid() === $orgaSid) {
                    $organization = $existingOrga;
                    break;
                }
            }
            if ($organization === null) {
                $organization = new Organization(Uuid::uuid4());
                $organization->setOrganizationSid($orgaSid);
                $this->entityManager->persist($organization);
            }
            $providerOrgaInfos = $this->orgaInfosProvider->retrieveInfos(new SpectrumIdentification($orgaSid));
            $organization->setAvatarUrl($providerOrgaInfos->avatarUrl);
            $organization->setName($providerOrgaInfos->fullname);
        }

        $this->entityManager->flush();
    }
}
