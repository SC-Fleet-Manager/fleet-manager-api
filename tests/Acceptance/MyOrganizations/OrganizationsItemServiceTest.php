<?php

namespace App\Tests\Acceptance\MyOrganizations;

use App\Application\MyOrganizations\OrganizationsItemService;
use App\Application\MyOrganizations\Output\OrganizationsItemFleetOutput;
use App\Application\MyOrganizations\Output\OrganizationsItemFleetShipsOutput;
use App\Application\MyOrganizations\Output\OrganizationsItemWithFleetOutput;
use App\Application\Repository\OrganizationFleetRepositoryInterface;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Exception\NotFoundOrganizationException;
use App\Domain\Exception\NotJoinedOrganizationMemberException;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Domain\OrganizationShipId;
use App\Domain\Service\EntityIdGeneratorInterface;
use App\Entity\Organization;
use App\Entity\OrganizationFleet;
use App\Infrastructure\Repository\Organization\InMemoryOrganizationFleetRepository;
use App\Infrastructure\Repository\Organization\InMemoryOrganizationRepository;
use App\Infrastructure\Service\InMemoryEntityIdGenerator;
use App\Tests\Acceptance\KernelTestCase;

class OrganizationsItemServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_return_infos_and_fleet_of_an_organization(): void
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
        $orga->addMember(MemberId::fromString('00000000-0000-0000-0000-000000000003'), true, new \DateTimeImmutable('2021-01-03T10:00:00Z'));
        $orga->addMember(MemberId::fromString('00000000-0000-0000-0000-000000000004'), false, new \DateTimeImmutable('2021-01-04T10:00:00Z'));

        /** @var InMemoryOrganizationFleetRepository $organizationFleetRepository */
        $organizationFleetRepository = static::$container->get(OrganizationFleetRepositoryInterface::class);
        $organizationFleetRepository->setOrganizationFleets([
            $orgaFleet = new OrganizationFleet($orgaId, new \DateTimeImmutable('2021-01-01T10:00:00Z')),
        ]);

        /** @var InMemoryEntityIdGenerator $entityIdGenerator */
        $entityIdGenerator = static::$container->get(EntityIdGeneratorInterface::class);
        $entityIdGenerator->setUid('00000000-0000-0000-0000-000000000020');
        $orgaFleet->createOrUpdateShip($memberId, 'Avenger', 'https://example.org/avenger_1.jpg', 3, new \DateTimeImmutable('2021-01-02T10:00:00Z'), $entityIdGenerator);
        $orgaFleet->createOrUpdateShip($memberId, 'Mercury Star Runner', null, 2, new \DateTimeImmutable('2021-01-03T10:00:00Z'), $entityIdGenerator);

        /** @var OrganizationsItemService $service */
        $service = static::$container->get(OrganizationsItemService::class);
        $output = $service->handle($orgaId, $memberId);

        static::assertEquals(new OrganizationsItemWithFleetOutput(
            $orgaId,
            'My orga',
            'ORG',
            null,
            false,
            new OrganizationsItemFleetOutput([
                new OrganizationsItemFleetShipsOutput(
                    'Avenger',
                    'https://example.org/avenger_1.jpg',
                    3,
                ),
                new OrganizationsItemFleetShipsOutput(
                    'Mercury Star Runner',
                    null,
                    2,
                ),
            ]),
        ), $output);
    }

    /**
     * @test
     */
    public function it_should_error_if_logged_user_not_joined(): void
    {
        $memberId = MemberId::fromString('00000000-0000-0000-0000-000000000001');
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

        $this->expectException(NotJoinedOrganizationMemberException::class);

        /** @var OrganizationsItemService $service */
        $service = static::$container->get(OrganizationsItemService::class);
        $service->handle($orgaId, $memberId);
    }

    /**
     * @test
     */
    public function it_should_error_if_unexistent_orga(): void
    {
        $memberId = MemberId::fromString('00000000-0000-0000-0000-000000000001');
        $orgaId = OrgaId::fromString('00000000-0000-0000-0000-000000000010');

        $this->expectException(NotFoundOrganizationException::class);

        /** @var OrganizationsItemService $service */
        $service = static::$container->get(OrganizationsItemService::class);
        $service->handle($orgaId, $memberId);
    }
}
