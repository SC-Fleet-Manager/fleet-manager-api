<?php

namespace App\Application\MyOrganizations;

use App\Application\Common\Clock;
use App\Application\Repository\OrganizationFleetRepositoryInterface;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Event\DeletedUserEvent;
use App\Domain\MemberId;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DeleteAccountHandler implements MessageHandlerInterface
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
        private OrganizationFleetRepositoryInterface $organizationFleetRepository,
        private Clock $clock,
    ) {
    }

    public function __invoke(DeletedUserEvent $event): void
    {
        $memberId = MemberId::fromString((string) $event->getUserId());

        $organizations = $this->organizationRepository->getOrganizationsByMember($memberId);

        $deletedOrganizationIds = [];
        $updatedOrganizations = [];
        $updatedOrganizationsIds = [];
        foreach ($organizations as $organization) {
            $organization->unjoinMember($memberId, $this->clock->now());
            if ($organization->hasNoMembers()) {
                $deletedOrganizationIds[] = $organization->getId();
            } else {
                $updatedOrganizations[] = $organization;
                $updatedOrganizationsIds[] = $organization->getId();
            }
        }
        $this->organizationRepository->saveAll($updatedOrganizations);
        $this->organizationRepository->deleteAll($deletedOrganizationIds);

        $fleets = $this->organizationFleetRepository->getOrganizationFleets($updatedOrganizationsIds);
        foreach ($fleets as $fleet) {
            $fleet->deleteShipsOfMember($memberId, $this->clock->now());
        }
        $this->organizationFleetRepository->saveAll($fleets);
        $this->organizationFleetRepository->deleteAll($deletedOrganizationIds);
    }
}
