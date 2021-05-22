<?php

namespace App\Tests\Acceptance\MyOrganizations;

use App\Application\MyOrganizations\DisbandOrganizationService;
use App\Application\Repository\OrganizationFleetRepositoryInterface;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Exception\NotFounderOfOrganizationException;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Domain\Service\EntityIdGeneratorInterface;
use App\Entity\Organization;
use App\Entity\OrganizationFleet;
use App\Infrastructure\Repository\Organization\InMemoryOrganizationFleetRepository;
use App\Infrastructure\Repository\Organization\InMemoryOrganizationRepository;
use App\Infrastructure\Service\InMemoryEntityIdGenerator;
use App\Tests\Acceptance\KernelTestCase;

class DisbandOrganizationServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_disband_the_organization_and_delete_its_fleet_and_ships(): void
    {
        $founderId = MemberId::fromString('00000000-0000-0000-0000-000000000001');
        $memberId = MemberId::fromString('00000000-0000-0000-0000-000000000002');
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
        $orga->addMember($memberId, true, new \DateTimeImmutable('2021-01-02T10:00:00Z'));

        /** @var InMemoryOrganizationFleetRepository $organizationFleetRepository */
        $organizationFleetRepository = static::$container->get(OrganizationFleetRepositoryInterface::class);
        $organizationFleetRepository->setOrganizationFleets([
            $orgaFleet = new OrganizationFleet($orgaId, new \DateTimeImmutable('2021-01-01T10:00:00Z')),
        ]);

        /** @var InMemoryEntityIdGenerator $entityIdGenerator */
        $entityIdGenerator = static::$container->get(EntityIdGeneratorInterface::class);
        $entityIdGenerator->setUid('00000000-0000-0000-0000-000000000020');
        $orgaFleet->createOrUpdateShip($founderId, 'Avenger', null, 2, new \DateTimeImmutable('2021-01-02T10:00:00Z'), $entityIdGenerator);
        $orgaFleet->createOrUpdateShip($memberId, 'Avenger', 'https://example.org/avenger_1.jpg', 3, new \DateTimeImmutable('2021-01-03T10:00:00Z'), $entityIdGenerator);

        /** @var DisbandOrganizationService $service */
        $service = static::$container->get(DisbandOrganizationService::class);
        $service->handle($orgaId, $founderId);

        static::assertNull($orgaRepository->getOrganization($orgaId), 'Orga should be deleted.');
        static::assertNull($organizationFleetRepository->getOrganizationFleet($orgaId), 'Orga fleet should be deleted.');
    }

    /**
     * @test
     */
    public function it_should_error_if_user_is_not_founder(): void
    {
        $founderId = MemberId::fromString('00000000-0000-0000-0000-000000000001');
        $memberId = MemberId::fromString('00000000-0000-0000-0000-000000000002');
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
        $orga->addMember($memberId, true, new \DateTimeImmutable('2021-01-02T10:00:00Z'));

        $this->expectException(NotFounderOfOrganizationException::class);

        /** @var DisbandOrganizationService $service */
        $service = static::$container->get(DisbandOrganizationService::class);
        $service->handle($orgaId, $memberId);
    }
}
