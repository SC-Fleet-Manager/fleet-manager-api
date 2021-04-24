<?php

namespace App\Tests\Acceptance\MyOrganizations;

use App\Application\MyOrganizations\AcceptCandidateService;
use App\Application\Provider\UserFleetProviderInterface;
use App\Application\Repository\OrganizationFleetRepositoryInterface;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Exception\NotFounderOfOrganizationException;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Domain\ShipId;
use App\Domain\UserFleet;
use App\Domain\UserId;
use App\Domain\UserShip;
use App\Entity\Organization;
use App\Infrastructure\Provider\Organizations\InMemoryUserFleetProvider;
use App\Infrastructure\Repository\Organization\InMemoryOrganizationFleetRepository;
use App\Infrastructure\Repository\Organization\InMemoryOrganizationRepository;
use App\Tests\Acceptance\KernelTestCase;

class AcceptCandidateServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_accept_candidate_of_an_organization(): void
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
            'org',
            null,
            new \DateTimeImmutable('2021-01-01T10:00:00Z'),
        ));
        $orga->addMember($memberId, false, new \DateTimeImmutable('2021-01-02T10:00:00Z'));

        /** @var InMemoryUserFleetProvider $userFleetProvider */
        $userFleetProvider = static::$container->get(UserFleetProviderInterface::class);
        $userFleetProvider->setUserFleet(new UserFleet(
            UserId::fromString((string) $memberId),
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

        /** @var AcceptCandidateService $service */
        $service = static::$container->get(AcceptCandidateService::class);
        $service->handle($orgaId, $founderId, $memberId);

        $orga = $orgaRepository->getOrganization($orgaId);
        static::assertTrue($orga->hasJoined($memberId), 'Candidate should have joined the organization.');

        /** @var InMemoryOrganizationFleetRepository $organizationFleetRepository */
        $organizationFleetRepository = static::$container->get(OrganizationFleetRepositoryInterface::class);
        $orgaFleet = $organizationFleetRepository->getOrganizationFleet($orgaId);
        static::assertSame(3, $orgaFleet->getShipByModel('Avenger Titan')->getQuantity());
        static::assertSame(1, $orgaFleet->getShipByModel('Mercury Star Runner')->getQuantity());
    }

    /**
     * @test
     */
    public function it_should_error_if_logged_user_is_not_founder(): void
    {
        $orgaId = OrgaId::fromString('00000000-0000-0000-0000-000000000010');
        $memberId = MemberId::fromString('00000000-0000-0000-0000-000000000001');
        $founderId = MemberId::fromString('00000000-0000-0000-0000-000000000002');

        /** @var InMemoryOrganizationRepository $orgaRepository */
        $orgaRepository = static::$container->get(OrganizationRepositoryInterface::class);
        $orga = new Organization(
            $orgaId,
            $founderId,
            'Force Coloniale Unifiée',
            'FCU',
            null,
            new \DateTimeImmutable('2021-01-01T10:00:00Z')
        );
        $orga->addMember($memberId, false, new \DateTimeImmutable('2021-01-01T11:00:00Z'));
        $orgaRepository->save($orga);

        $this->expectException(NotFounderOfOrganizationException::class);

        /** @var AcceptCandidateService $service */
        $service = static::$container->get(AcceptCandidateService::class);
        $service->handle($orgaId, $memberId, $memberId);
    }
}
