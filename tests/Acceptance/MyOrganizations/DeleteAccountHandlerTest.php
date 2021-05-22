<?php

namespace App\Tests\Acceptance\MyOrganizations;

use App\Application\MyOrganizations\DeleteAccountHandler;
use App\Application\Repository\OrganizationFleetRepositoryInterface;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Event\DeletedUserEvent;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Domain\Service\EntityIdGeneratorInterface;
use App\Domain\UserId;
use App\Entity\Organization;
use App\Entity\OrganizationFleet;
use App\Infrastructure\Repository\Organization\InMemoryOrganizationFleetRepository;
use App\Infrastructure\Repository\Organization\InMemoryOrganizationRepository;
use App\Infrastructure\Service\InMemoryEntityIdGenerator;
use App\Tests\Acceptance\KernelTestCase;

class DeleteAccountHandlerTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_delete_the_fleet_and_ships_of_deleted_user_in_its_organizations(): void
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

        /** @var InMemoryEntityIdGenerator $entityIdGenerator */
        $entityIdGenerator = static::$container->get(EntityIdGeneratorInterface::class);
        $entityIdGenerator->setUid('00000000-0000-0000-0000-000000000020');
        $orgaFleet->createOrUpdateShip($memberId, 'Avenger', 'https://example.org/avenger_1.jpg', 3, new \DateTimeImmutable('2021-01-02T10:00:00Z'), $entityIdGenerator);
        $orgaFleet->createOrUpdateShip(MemberId::fromString('00000000-0000-0000-0000-000000000002'), 'Avenger', null, 2, new \DateTimeImmutable('2021-01-03T10:00:00Z'), $entityIdGenerator);

        static::$container->get(DeleteAccountHandler::class)(new DeletedUserEvent(UserId::fromString((string) $memberId), 'Ioni'));

        static::assertFalse($orgaRepository->getOrganization($orgaId)->isMemberOf($memberId), 'User should not member anymore.');

        $organizationFleet = $organizationFleetRepository->getOrganizationFleet($orgaId);
        static::assertSame(2, $organizationFleet->getShipByModel('Avenger')->getQuantity());
    }

    /**
     * @test
     */
    public function it_should_delete_all_the_organization_if_deleted_user_is_founder_and_only_member(): void
    {
        $memberId = MemberId::fromString('00000000-0000-0000-0000-000000000001');
        $orgaId = OrgaId::fromString('00000000-0000-0000-0000-000000000010');

        /** @var InMemoryOrganizationRepository $orgaRepository */
        $orgaRepository = static::$container->get(OrganizationRepositoryInterface::class);
        $orgaRepository->save(new Organization(
            $orgaId,
            $memberId,
            'My orga',
            'ORG',
            null,
            new \DateTimeImmutable('2021-01-01T10:00:00Z'),
        ));

        /** @var InMemoryOrganizationFleetRepository $organizationFleetRepository */
        $organizationFleetRepository = static::$container->get(OrganizationFleetRepositoryInterface::class);
        $organizationFleetRepository->setOrganizationFleets([
            $orgaFleet = new OrganizationFleet($orgaId, new \DateTimeImmutable('2021-01-01T10:00:00Z')),
        ]);

        /** @var InMemoryEntityIdGenerator $entityIdGenerator */
        $entityIdGenerator = static::$container->get(EntityIdGeneratorInterface::class);
        $entityIdGenerator->setUid('00000000-0000-0000-0000-000000000020');
        $orgaFleet->createOrUpdateShip($memberId, 'Avenger', 'https://example.org/avenger_1.jpg', 3, new \DateTimeImmutable('2021-01-02T10:00:00Z'), $entityIdGenerator);

        static::$container->get(DeleteAccountHandler::class)(new DeletedUserEvent(UserId::fromString((string) $memberId), 'Ioni'));

        static::assertNull($orgaRepository->getOrganization($orgaId), 'Orga should be deleted.');

        $organizationFleet = $organizationFleetRepository->getOrganizationFleet($orgaId);
        static::assertNull($organizationFleet, 'Orga fleet should be deleted.');
    }

    /**
     * @test
     */
    public function it_should_promote_new_founder_if_deleted_user_was_founder_and_not_only_member(): void
    {
        $memberId = MemberId::fromString('00000000-0000-0000-0000-000000000001');
        $founderId = MemberId::fromString('00000000-0000-0000-0000-000000000002');
        $orgaId = OrgaId::fromString('00000000-0000-0000-0000-000000000010');

        /** @var InMemoryOrganizationRepository $orgaRepository */
        $orgaRepository = static::$container->get(OrganizationRepositoryInterface::class);
        $orgaRepository->save($orga = new Organization(
            $orgaId,
            $founderId,
            'My orga',
            'ORG',
            null,
            new \DateTimeImmutable('2021-01-01T10:00:00Z'),
        ));
        $orga->addMember($memberId, true, new \DateTimeImmutable('2021-01-01T10:00:00Z'));

        /** @var InMemoryOrganizationFleetRepository $organizationFleetRepository */
        $organizationFleetRepository = static::$container->get(OrganizationFleetRepositoryInterface::class);
        $organizationFleetRepository->setOrganizationFleets([
            $orgaFleet = new OrganizationFleet($orgaId, new \DateTimeImmutable('2021-01-01T10:00:00Z')),
        ]);

        /** @var InMemoryEntityIdGenerator $entityIdGenerator */
        $entityIdGenerator = static::$container->get(EntityIdGeneratorInterface::class);
        $entityIdGenerator->setUid('00000000-0000-0000-0000-000000000020');
        $orgaFleet->createOrUpdateShip($founderId, 'Avenger', null, 3, new \DateTimeImmutable('2021-01-02T10:00:00Z'), $entityIdGenerator);
        $orgaFleet->createOrUpdateShip($memberId, 'Avenger', null, 2, new \DateTimeImmutable('2021-01-03T10:00:00Z'), $entityIdGenerator);

        static::$container->get(DeleteAccountHandler::class)(new DeletedUserEvent(UserId::fromString((string) $founderId), 'Ioni'));

        $orga = $orgaRepository->getOrganization($orgaId);
        static::assertFalse($orga->isMemberOf($founderId), 'User should not member anymore.');

        static::assertTrue($orga->isFounder($memberId), 'The other member should be promoted to founder.');
        $organizationFleet = $organizationFleetRepository->getOrganizationFleet($orgaId);
        static::assertSame(2, $organizationFleet->getShipByModel('Avenger')->getQuantity());
    }
}
