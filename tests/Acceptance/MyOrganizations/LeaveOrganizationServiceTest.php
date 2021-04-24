<?php

namespace App\Tests\Acceptance\MyOrganizations;

use App\Application\MyOrganizations\LeaveOrganizationService;
use App\Application\Repository\OrganizationFleetRepositoryInterface;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Exception\FounderOfOrganizationException;
use App\Domain\Exception\NotMemberOfOrganizationException;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Domain\OrganizationShipId;
use App\Entity\Organization;
use App\Entity\OrganizationFleet;
use App\Infrastructure\Repository\Organization\InMemoryOrganizationFleetRepository;
use App\Infrastructure\Repository\Organization\InMemoryOrganizationRepository;
use App\Tests\Acceptance\KernelTestCase;

class LeaveOrganizationServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_leave_the_organization_and_delete_the_member_fleet_and_ships_off_the_orga(): void
    {
        $memberId = MemberId::fromString('00000000-0000-0000-0000-000000000001');
        $orgaId = OrgaId::fromString('00000000-0000-0000-0000-000000000010');

        /** @var InMemoryOrganizationRepository $orgaRepository */
        $orgaRepository = static::$container->get(OrganizationRepositoryInterface::class);
        $orgaRepository->save($orga = new Organization(
            $orgaId,
            MemberId::fromString('00000000-0000-0000-0000-000000000002'),
            'My orga',
            'ORG',
            null,
            new \DateTimeImmutable('2021-01-01T10:00:00Z'),
        ));
        $orga->addMember($memberId, true, new \DateTimeImmutable('2021-01-02T10:00:00Z'));

        /** @var InMemoryOrganizationFleetRepository $organizationFleetRepository */
        $organizationFleetRepository = static::$container->get(OrganizationFleetRepositoryInterface::class);
        $organizationFleetRepository->setOrganizationFleets([
            $orgaFleet = new OrganizationFleet($orgaId, new \DateTimeImmutable('2021-01-01T10:00:00Z')),
        ]);
        $orgaFleet->createOrUpdateShip(OrganizationShipId::fromString('00000000-0000-0000-0000-000000000020'), $memberId, 'Avenger', 'https://example.org/avenger_1.jpg', 3, new \DateTimeImmutable('2021-01-02T10:00:00Z'));
        $orgaFleet->createOrUpdateShip(OrganizationShipId::fromString('00000000-0000-0000-0000-000000000020'), MemberId::fromString('00000000-0000-0000-0000-000000000002'), 'Avenger', null, 2, new \DateTimeImmutable('2021-01-03T10:00:00Z'));

        /** @var LeaveOrganizationService $service */
        $service = static::$container->get(LeaveOrganizationService::class);
        $service->handle($orgaId, $memberId);

        static::assertFalse($orgaRepository->getOrganization($orgaId)->isMemberOf($memberId), 'User should not member anymore.');

        $organizationFleet = $organizationFleetRepository->getOrganizationFleet($orgaId);
        static::assertSame(2, $organizationFleet->getShipByModel('Avenger')->getQuantity());
    }

    /**
     * @test
     */
    public function it_should_error_if_member_is_not_member(): void
    {
        $notMemberId = MemberId::fromString('00000000-0000-0000-0000-000000000001');
        $orgaId = OrgaId::fromString('00000000-0000-0000-0000-000000000010');

        /** @var InMemoryOrganizationRepository $orgaRepository */
        $orgaRepository = static::$container->get(OrganizationRepositoryInterface::class);
        $orgaRepository->save(new Organization(
            $orgaId,
            MemberId::fromString('00000000-0000-0000-0000-000000000002'),
            'My orga',
            'ORG',
            null,
            new \DateTimeImmutable('2021-01-01T10:00:00Z'),
        ));

        $this->expectException(NotMemberOfOrganizationException::class);

        /** @var LeaveOrganizationService $service */
        $service = static::$container->get(LeaveOrganizationService::class);
        $service->handle($orgaId, $notMemberId);
    }

    /**
     * @test
     */
    public function it_should_error_if_member_is_founder(): void
    {
        $memberId = MemberId::fromString('00000000-0000-0000-0000-000000000001');
        $orgaId = OrgaId::fromString('00000000-0000-0000-0000-000000000010');

        /** @var InMemoryOrganizationRepository $orgaRepository */
        $orgaRepository = static::$container->get(OrganizationRepositoryInterface::class);
        $orgaRepository->save($orga = new Organization(
            $orgaId,
            $memberId,
            'My orga',
            'ORG',
            null,
            new \DateTimeImmutable('2021-01-01T10:00:00Z'),
        ));
        $orga->addMember($memberId, true, new \DateTimeImmutable('2021-01-02T10:00:00Z'));

        $this->expectException(FounderOfOrganizationException::class);

        /** @var LeaveOrganizationService $service */
        $service = static::$container->get(LeaveOrganizationService::class);
        $service->handle($orgaId, $memberId);
    }
}
