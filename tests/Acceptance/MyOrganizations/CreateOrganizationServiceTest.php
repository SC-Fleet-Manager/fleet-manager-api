<?php

namespace App\Tests\Acceptance\MyOrganizations;

use App\Application\MyOrganizations\CreateOrganizationService;
use App\Application\Provider\MemberProfileProviderInterface;
use App\Application\Provider\UserFleetProviderInterface;
use App\Application\Repository\OrganizationFleetRepositoryInterface;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Exception\NoMemberHandleException;
use App\Domain\MemberId;
use App\Domain\MemberProfile;
use App\Domain\OrgaId;
use App\Domain\ShipId;
use App\Domain\UserFleet;
use App\Domain\UserId;
use App\Domain\UserShip;
use App\Infrastructure\Provider\Organizations\InMemoryMemberProfileProvider;
use App\Infrastructure\Provider\Organizations\InMemoryUserFleetProvider;
use App\Infrastructure\Repository\Organization\InMemoryOrganizationFleetRepository;
use App\Infrastructure\Repository\Organization\InMemoryOrganizationRepository;
use App\Tests\Acceptance\KernelTestCase;

class CreateOrganizationServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_create_an_orga_of_logged_user(): void
    {
        $memberId = MemberId::fromString('00000000-0000-0000-0000-000000000001');
        $orgaId = OrgaId::fromString('00000000-0000-0000-0000-000000000010');

        /** @var InMemoryUserFleetProvider $userFleetProvider */
        $userFleetProvider = static::$container->get(UserFleetProviderInterface::class);
        $userFleetProvider->setUserFleet(new UserFleet(
            UserId::fromString('00000000-0000-0000-0000-000000000001'),
            [
                new UserShip(
                    ShipId::fromString('00000000-0000-0000-0000-000000000020'),
                    'Avenger Titan',
                    'https://example.org/avenger.jpg',
                    3,
                ),
                new UserShip(
                    ShipId::fromString('00000000-0000-0000-0000-000000000021'),
                    'Mercury Star Runner',
                    null,
                    1,
                ),
            ],
        ));

        /** @var InMemoryMemberProfileProvider $memberProfileProvider */
        $memberProfileProvider = static::$container->get(MemberProfileProviderInterface::class);
        $memberProfileProvider->setProfiles([
            new MemberProfile($memberId, null, 'handle'),
        ]);

        /** @var CreateOrganizationService $service */
        $service = static::$container->get(CreateOrganizationService::class);
        $service->handle($orgaId, $memberId, 'Force Coloniale Unifiée', 'fcu', 'https://example.org/picture.jpg');

        /** @var InMemoryOrganizationRepository $orgaRepository */
        $orgaRepository = static::$container->get(OrganizationRepositoryInterface::class);
        $orga = $orgaRepository->getOrganization($orgaId);
        static::assertNotNull($orga, 'Orga should be created.');
        static::assertSame('00000000-0000-0000-0000-000000000010', (string) $orga->getId());

        /** @var InMemoryOrganizationFleetRepository $orgaFleetRepository */
        $orgaFleetRepository = static::$container->get(OrganizationFleetRepositoryInterface::class);
        $orgaFleet = $orgaFleetRepository->getOrganizationFleet($orgaId);
        static::assertEquals($orgaId, $orgaFleet->getOrgaId());
        static::assertCount(2, $ships = $orgaFleet->getShips());
        $ship = array_shift($ships);
        static::assertSame('Avenger Titan', $ship->getModel());
        static::assertSame(3, $ship->getQuantity());
        $ship = array_shift($ships);
        static::assertSame('Mercury Star Runner', $ship->getModel());
        static::assertSame(1, $ship->getQuantity());
    }

    /**
     * @test
     */
    public function it_should_error_if_logged_user_has_no_handle(): void
    {
        $memberId = MemberId::fromString('00000000-0000-0000-0000-000000000001');
        $orgaId = OrgaId::fromString('00000000-0000-0000-0000-000000000010');

//        /** @var InMemoryUserFleetProvider $userFleetProvider */
//        $userFleetProvider = static::$container->get(UserFleetProviderInterface::class);
//        $userFleetProvider->setUserFleet(new UserFleet(
//            UserId::fromString('00000000-0000-0000-0000-000000000001'),
//            [
//                new UserShip(
//                    ShipId::fromString('00000000-0000-0000-0000-000000000020'),
//                    'Avenger Titan',
//                    'https://example.org/avenger.jpg',
//                    3,
//                ),
//                new UserShip(
//                    ShipId::fromString('00000000-0000-0000-0000-000000000021'),
//                    'Mercury Star Runner',
//                    null,
//                    1,
//                ),
//            ],
//        ));

        /** @var InMemoryMemberProfileProvider $memberProfileProvider */
        $memberProfileProvider = static::$container->get(MemberProfileProviderInterface::class);
        $memberProfileProvider->setProfiles([
            new MemberProfile($memberId, null, null),
        ]);

        $this->expectException(NoMemberHandleException::class);

        /** @var CreateOrganizationService $service */
        $service = static::$container->get(CreateOrganizationService::class);
        $service->handle($orgaId, $memberId, 'Force Coloniale Unifiée', 'fcu', 'https://example.org/picture.jpg');
    }
}
