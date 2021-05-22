<?php

namespace App\Tests\Acceptance\MyOrganizations;

use App\Application\MyOrganizations\OrganizationShipOwnersService;
use App\Application\MyOrganizations\Output\OrganizationShipOwnersItemOutput;
use App\Application\MyOrganizations\Output\OrganizationShipOwnersOutput;
use App\Application\Provider\MemberProfileProviderInterface;
use App\Application\Repository\OrganizationFleetRepositoryInterface;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\MemberId;
use App\Domain\MemberProfile;
use App\Domain\OrgaId;
use App\Domain\Service\EntityIdGeneratorInterface;
use App\Entity\Organization;
use App\Entity\OrganizationFleet;
use App\Infrastructure\Provider\Organizations\InMemoryMemberProfileProvider;
use App\Infrastructure\Repository\Organization\InMemoryOrganizationFleetRepository;
use App\Infrastructure\Repository\Organization\InMemoryOrganizationRepository;
use App\Infrastructure\Service\InMemoryEntityIdGenerator;
use App\Tests\Acceptance\KernelTestCase;

class OrganizationShipOwnersServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_return_list_of_owners_of_an_orga_ship(): void
    {
        $shipModel = 'Avenger';
        $memberId = MemberId::fromString('00000000-0000-0000-0000-000000000003');
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
        $orga->addMember($memberId, true, new \DateTimeImmutable('2021-01-03T10:00:00Z'));
        $orga->addMember(MemberId::fromString('00000000-0000-0000-0000-000000000004'), false, new \DateTimeImmutable('2021-01-04T10:00:00Z'));

        /** @var InMemoryMemberProfileProvider $memberProfileProvider */
        $memberProfileProvider = static::$container->get(MemberProfileProviderInterface::class);
        $memberProfileProvider->setProfiles([
            new MemberProfile(MemberId::fromString('00000000-0000-0000-0000-000000000002'), 'Member 1', 'member1'),
            new MemberProfile($memberId, 'Member 2', 'member2'),
        ]);

        /** @var InMemoryOrganizationFleetRepository $organizationFleetRepository */
        $organizationFleetRepository = static::$container->get(OrganizationFleetRepositoryInterface::class);
        $organizationFleetRepository->setOrganizationFleets([
            $orgaFleet = new OrganizationFleet($orgaId, new \DateTimeImmutable('2021-01-01T10:00:00Z')),
        ]);

        /** @var InMemoryEntityIdGenerator $entityIdGenerator */
        $entityIdGenerator = static::$container->get(EntityIdGeneratorInterface::class);
        $entityIdGenerator->setUid('00000000-0000-0000-0000-000000000020');
        $orgaFleet->createOrUpdateShip(MemberId::fromString('00000000-0000-0000-0000-000000000002'), 'Avenger', 'https://example.org/avenger_1.jpg', 3, new \DateTimeImmutable('2021-01-02T10:00:00Z'), $entityIdGenerator);
        $orgaFleet->createOrUpdateShip($memberId, 'avênger', null, 2, new \DateTimeImmutable('2021-01-03T10:00:00Z'), $entityIdGenerator);
        $orgaFleet->createOrUpdateShip($memberId, 'Mercury Star Runner', null, 2, new \DateTimeImmutable('2021-01-04T10:00:00Z'), $entityIdGenerator);

        /** @var OrganizationShipOwnersService $service */
        $service = static::$container->get(OrganizationShipOwnersService::class);
        $output = $service->handle($orgaId, $memberId, $shipModel);

        static::assertEquals(new OrganizationShipOwnersOutput(
            [
                new OrganizationShipOwnersItemOutput(MemberId::fromString('00000000-0000-0000-0000-000000000002'), 3, 'Member 1', 'member1'),
                new OrganizationShipOwnersItemOutput($memberId, 2, 'Member 2', 'member2'),
            ],
        ), $output);
    }
}
