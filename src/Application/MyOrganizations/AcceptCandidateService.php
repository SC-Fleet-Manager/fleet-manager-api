<?php

namespace App\Application\MyOrganizations;

use App\Application\Common\Clock;
use App\Application\Provider\UserFleetProviderInterface;
use App\Application\Repository\OrganizationFleetRepositoryInterface;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Exception\NotFounderOfOrganizationException;
use App\Domain\Exception\NotFoundOrganizationException;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Domain\OrganizationShipId;
use App\Entity\OrganizationFleet;
use Symfony\Component\Uid\Ulid;

class AcceptCandidateService
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
        private OrganizationFleetRepositoryInterface $organizationFleetRepository,
        private UserFleetProviderInterface $userFleetProvider,
        private Clock $clock,
    ) {
    }

    public function handle(OrgaId $orgaId, MemberId $founderId, MemberId $candidateId): void
    {
        $organization = $this->organizationRepository->getOrganization($orgaId);
        if ($organization === null) {
            throw new NotFoundOrganizationException($orgaId);
        }

        if (!$organization->isFounder($founderId)) {
            throw new NotFounderOfOrganizationException($orgaId, $founderId);
        }

        $organization->acceptCandidate($candidateId, $this->clock->now());
        $this->organizationRepository->save($organization);

        $memberFleet = $this->userFleetProvider->getUserFleet($candidateId);
        $orgaFleet = $this->organizationFleetRepository->getOrganizationFleet($orgaId);
        if ($orgaFleet === null) {
            $orgaFleet = new OrganizationFleet($orgaId, $this->clock->now());
        }
        foreach ($memberFleet->getShips() as $userShip) {
            $orgaFleet->createOrUpdateShip(
            // TODO : use service to generate Ulid
                new OrganizationShipId(new Ulid()),
                $candidateId,
                $userShip->getModel(),
                $userShip->getImageUrl(),
                $userShip->getQuantity(),
                $this->clock->now(),
            );
        }

        $this->organizationFleetRepository->saveAll([$orgaFleet]);
    }
}
