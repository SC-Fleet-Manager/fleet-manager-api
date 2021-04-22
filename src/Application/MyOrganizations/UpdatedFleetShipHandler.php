<?php

namespace App\Application\MyOrganizations;

use App\Application\Common\Clock;
use App\Application\Repository\OrganizationFleetRepositoryInterface;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Event\UpdatedFleetShipEvent;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Domain\OrganizationShipId;
use App\Entity\Organization;
use App\Entity\OrganizationFleet;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Uid\Ulid;

class UpdatedFleetShipHandler implements MessageHandlerInterface
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
        private OrganizationFleetRepositoryInterface $organizationFleetRepository,
        private Clock $clock,
    ) {
    }

    public function __invoke(UpdatedFleetShipEvent $event): void
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

        $existingOrgaIds = array_map(static function (OrganizationFleet $orgaFleet): OrgaId {
            return $orgaFleet->getOrgaId();
        }, $organizationFleets);

        $organizationFleets = array_merge($organizationFleets,
            $this->createOrganizationFleets(array_diff($orgaIds, $existingOrgaIds))
        );

        foreach ($organizationFleets as $organizationFleet) {
            $organizationFleet->createOrUpdateShip(
                new OrganizationShipId(new Ulid()),
                $memberId,
                $event->model,
                $event->logoUrl,
                $event->quantity,
                $this->clock->now(),
            );
        }
        $this->organizationFleetRepository->saveAll($organizationFleets);
    }

    /**
     * @param OrgaId[] $orgaIds
     *
     * @return OrganizationFleet[]
     */
    private function createOrganizationFleets(array $orgaIds): array
    {
        $result = [];
        foreach ($orgaIds as $orgaId) {
            $result[] = new OrganizationFleet($orgaId, $this->clock->now());
        }

        return $result;
    }
}
