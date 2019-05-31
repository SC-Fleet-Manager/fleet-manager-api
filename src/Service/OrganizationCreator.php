<?php

namespace App\Service;

use App\Entity\CitizenOrganization;
use App\Entity\Organization;
use Doctrine\ORM\EntityManagerInterface;
use Ramsey\Uuid\Uuid;

class OrganizationCreator
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Creates an Organization for each not existing given orgas.
     *
     * @param CitizenOrganization[] $organizations
     */
    public function createOrganization(array $organizations): void
    {
        $sids = array_map(static function (CitizenOrganization $orga) {
            return $orga->getOrganizationSid();
        }, $organizations);

        /** @var Organization[] $existingOrgas */
        $existingOrgas = $this->entityManager->getRepository(Organization::class)->findBy(['organizationSid' => $sids]);
        foreach ($organizations as $orga) {
            $found = false;
            foreach ($existingOrgas as $existingOrga) {
                if ($existingOrga->getOrganizationSid() === $orga->getOrganizationSid()) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $newOrga = new Organization(Uuid::uuid4());
                $newOrga->setOrganizationSid($orga->getOrganizationSid());
                $this->entityManager->persist($newOrga);
            }
        }

        $this->entityManager->flush();
    }
}
