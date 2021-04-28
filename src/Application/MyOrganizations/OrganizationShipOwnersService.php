<?php

namespace App\Application\MyOrganizations;

use App\Application\MyOrganizations\Output\OrganizationShipOwnersItemOutput;
use App\Application\MyOrganizations\Output\OrganizationShipOwnersOutput;
use App\Application\Provider\MemberProfileProviderInterface;
use App\Application\Repository\OrganizationFleetRepositoryInterface;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Exception\NotFoundOrganizationException;
use App\Domain\Exception\NotFoundOrganizationFleetException;
use App\Domain\Exception\NotJoinedOrganizationMemberException;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Entity\OrganizationShipMember;

class OrganizationShipOwnersService
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
        private OrganizationFleetRepositoryInterface $organizationFleetRepository,
        private MemberProfileProviderInterface $memberProfileProvider,
    ) {
    }

    public function handle(OrgaId $orgaId, MemberId $memberId, string $shipModel): OrganizationShipOwnersOutput
    {
        $organization = $this->organizationRepository->getOrganization($orgaId);
        if ($organization === null) {
            throw new NotFoundOrganizationException($orgaId);
        }
        if (!$organization->hasJoined($memberId)) {
            throw new NotJoinedOrganizationMemberException($orgaId, $memberId);
        }

        $fleet = $this->organizationFleetRepository->getOrganizationFleet($orgaId);
        if ($fleet === null) {
            throw new NotFoundOrganizationFleetException($orgaId);
        }
        $ship = $fleet->getShipByModel($shipModel);
        if ($ship === null) {
            return new OrganizationShipOwnersOutput([]);
        }
        $owners = $ship->getOwners();

        $ownerIds = array_map(static function (OrganizationShipMember $member): MemberId {
            return $member->getMemberId();
        }, $owners);
        $profiles = $this->memberProfileProvider->getProfiles($ownerIds);

        $ownerOutputs = [];
        foreach ($owners as $ownerId => $owner) {
            $ownerOutputs[] = new OrganizationShipOwnersItemOutput(
                $owner->getMemberId(),
                $owner->getQuantity(),
                isset($profiles[$ownerId]) ? $profiles[$ownerId]->getNickname() : null,
                isset($profiles[$ownerId]) ? $profiles[$ownerId]->getHandle() : null,
            );
        }

        return new OrganizationShipOwnersOutput($ownerOutputs);
    }
}
