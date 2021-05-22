<?php

namespace App\Application\MyOrganizations;

use App\Application\Common\Clock;
use App\Application\Repository\OrganizationFleetRepositoryInterface;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Event\UpdatedFleetEvent;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Domain\Service\EntityIdGeneratorInterface;
use App\Entity\Organization;
use App\Entity\OrganizationFleet;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class UpdatedFleetHandler implements MessageHandlerInterface
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
        private OrganizationFleetRepositoryInterface $organizationFleetRepository,
        private EntityIdGeneratorInterface $entityIdGenerator,
        private Clock $clock,
    ) {
    }

    public function __invoke(UpdatedFleetEvent $event): void
    {
        $memberId = MemberId::fromString((string) $event->ownerId);

        $organizationFleets = $this->getOrganizationFleetsToUpdate($memberId);

        foreach ($organizationFleets as $organizationFleet) {
            if (!$organizationFleet->isNewMemberFleetVersion($memberId, $event->version)) {
                continue;
            }
            $organizationFleet->updateMemberFleet(
                $memberId,
                $event->ships,
                $event->version,
                $this->clock->now(),
                $this->entityIdGenerator,
            );
        }
        $this->organizationFleetRepository->saveAll($organizationFleets);
    }

    /**
     * @return OrganizationFleet[]
     */
    private function getOrganizationFleetsToUpdate(MemberId $memberId): array
    {
        $orgaIds = $this->getJoinedOrganizationIds($memberId);
        $organizationFleets = $this->organizationFleetRepository->getOrganizationFleets($orgaIds);

        $existingOrgaIds = array_map(static function (OrganizationFleet $orgaFleet): OrgaId {
            return $orgaFleet->getOrgaId();
        }, $organizationFleets);

        return array_merge($organizationFleets,
            $this->createOrganizationFleets(array_diff($orgaIds, $existingOrgaIds))
        );
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

    /**
     * @return OrgaId[]
     */
    private function getJoinedOrganizationIds(MemberId $memberId): array
    {
        $organizations = $this->organizationRepository->getOrganizationsByMember($memberId);
        $organizations = array_filter($organizations, static function (Organization $organization) use ($memberId): bool {
            return $organization->hasJoined($memberId);
        });

        return array_map(static function (Organization $organization): OrgaId {
            return $organization->getId();
        }, $organizations);
    }
}
