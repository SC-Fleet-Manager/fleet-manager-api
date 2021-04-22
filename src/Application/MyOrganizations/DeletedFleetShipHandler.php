<?php

namespace App\Application\MyOrganizations;

use App\Application\Common\Clock;
use App\Application\Repository\OrganizationFleetRepositoryInterface;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Event\DeletedFleetShipEvent;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Entity\Organization;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DeletedFleetShipHandler implements MessageHandlerInterface
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
        private OrganizationFleetRepositoryInterface $organizationFleetRepository,
        private Clock $clock,
    ) {
    }

    public function __invoke(DeletedFleetShipEvent $event): void
    {
        $memberId = MemberId::fromString((string) $event->ownerId);

        $organizations = $this->organizationRepository->getOrganizationsByMember($memberId);
        $organizations = array_filter($organizations, static function (Organization $organization) use ($memberId): bool {
            return $organization->hasJoined($memberId);
        });

        $orgaIds = array_map(static function (Organization $organization): OrgaId {
            return $organization->getId();
        }, $organizations);
        $organizationFleets = $this->organizationFleetRepository->getOrganizationFleets($orgaIds);

        foreach ($organizationFleets as $organizationFleet) {
            $organizationFleet->deleteShip(
                $memberId,
                $event->model,
                $this->clock->now(),
            );
        }
        $this->organizationFleetRepository->saveAll($organizationFleets);
    }
}
