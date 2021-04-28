<?php

namespace App\Application\MyOrganizations;

use App\Application\Common\Clock;
use App\Application\Provider\MemberProfileProviderInterface;
use App\Application\Provider\UserFleetProviderInterface;
use App\Application\Repository\OrganizationFleetRepositoryInterface;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Exception\NoMemberHandleException;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Domain\OrganizationShipId;
use App\Entity\Organization;
use App\Entity\OrganizationFleet;
use Symfony\Component\Uid\Ulid;

class CreateOrganizationService
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
        private OrganizationFleetRepositoryInterface $organizationFleetRepository,
        private UserFleetProviderInterface $userFleetProvider,
        private MemberProfileProviderInterface $memberProfileProvider,
        private Clock $clock,
    ) {
    }

    public function handle(OrgaId $orgaId, MemberId $memberId, string $name, string $sid, ?string $logoUrl = null): void
    {
        $memberProfile = $this->memberProfileProvider->getProfiles([$memberId])[(string) $memberId] ?? null;
        if ($memberProfile === null || $memberProfile->getHandle() === null) {
            throw new NoMemberHandleException($memberId);
        }

        $orga = $this->organizationRepository->getOrganization($orgaId);
        if ($orga === null) {
            $orga = new Organization($orgaId, $memberId, $name, $sid, $logoUrl, $this->clock->now());
            $this->organizationRepository->save($orga);
        }

        $memberFleet = $this->userFleetProvider->getUserFleet($memberId);

        $orgaFleet = new OrganizationFleet($orga->getId(), $this->clock->now());
        foreach ($memberFleet->getShips() as $userShip) {
            $orgaFleet->createOrUpdateShip(
            // TODO : use service to generate Ulid
                new OrganizationShipId(new Ulid()),
                $memberId,
                $userShip->getModel(),
                $userShip->getImageUrl(),
                $userShip->getQuantity(),
                $this->clock->now(),
            );
        }

        $this->organizationFleetRepository->saveAll([$orgaFleet]);
    }
}
