<?php

namespace App\Tests\Acceptance\MyOrganizations;

use App\Application\MyOrganizations\UnjoinOrganizationService;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Exception\FullyJoinedMemberOfOrganizationException;
use App\Domain\Exception\NotFoundOrganizationException;
use App\Domain\Exception\NotMemberOfOrganizationException;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Entity\Organization;
use App\Infrastructure\Repository\Organization\InMemoryOrganizationRepository;
use App\Tests\Acceptance\KernelTestCase;

class UnjoinOrganizationServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_unjoin_an_orga_that_logged_user_has_not_yet_joined(): void
    {
        $memberId = MemberId::fromString('00000000-0000-0000-0000-000000000001');
        $orgaId = OrgaId::fromString('00000000-0000-0000-0000-000000000010');

        /** @var InMemoryOrganizationRepository $orgaRepository */
        $orgaRepository = static::$container->get(OrganizationRepositoryInterface::class);
        $orgaRepository->save($orga = new Organization(
            $orgaId,
            MemberId::fromString('00000000-0000-0000-0000-000000000002'),
            'My orga',
            'org',
            null,
            new \DateTimeImmutable('2021-01-01T10:00:00Z'),
        ));
        $orga->addMember($memberId, false, new \DateTimeImmutable('2021-01-02T10:00:00Z'));

        /** @var UnjoinOrganizationService $service */
        $service = static::$container->get(UnjoinOrganizationService::class);
        $service->handle($orgaId, $memberId);

        $orga = $orgaRepository->getOrganization($orgaId);
        static::assertFalse($orga->isMemberOf($memberId));
    }

    /**
     * @test
     */
    public function it_should_error_if_unjoin_an_orga_that_logged_user_has_fully_joined(): void
    {
        $memberId = MemberId::fromString('00000000-0000-0000-0000-000000000001');
        $orgaId = OrgaId::fromString('00000000-0000-0000-0000-000000000010');

        /** @var InMemoryOrganizationRepository $orgaRepository */
        $orgaRepository = static::$container->get(OrganizationRepositoryInterface::class);
        $orgaRepository->save($orga = new Organization(
            $orgaId,
            MemberId::fromString('00000000-0000-0000-0000-000000000002'),
            'My orga',
            'org',
            null,
            new \DateTimeImmutable('2021-01-01T10:00:00Z'),
        ));
        $orga->addMember($memberId, true, new \DateTimeImmutable('2021-01-02T10:00:00Z'));

        $this->expectException(FullyJoinedMemberOfOrganizationException::class);

        /** @var UnjoinOrganizationService $service */
        $service = static::$container->get(UnjoinOrganizationService::class);
        $service->handle($orgaId, $memberId);
    }

    /**
     * @test
     */
    public function it_should_error_if_unjoin_an_orga_that_logged_user_has_not_joined_yet(): void
    {
        $memberId = MemberId::fromString('00000000-0000-0000-0000-000000000001');
        $orgaId = OrgaId::fromString('00000000-0000-0000-0000-000000000010');

        /** @var InMemoryOrganizationRepository $orgaRepository */
        $orgaRepository = static::$container->get(OrganizationRepositoryInterface::class);
        $orgaRepository->save(new Organization(
            $orgaId,
            MemberId::fromString('00000000-0000-0000-0000-000000000002'),
            'My orga',
            'org',
            null,
            new \DateTimeImmutable('2021-01-01T10:00:00Z'),
        ));

        $this->expectException(NotMemberOfOrganizationException::class);

        /** @var UnjoinOrganizationService $service */
        $service = static::$container->get(UnjoinOrganizationService::class);
        $service->handle($orgaId, $memberId);
    }

    /**
     * @test
     */
    public function it_should_error_if_unjoin_unexistent_orga(): void
    {
        $memberId = MemberId::fromString('00000000-0000-0000-0000-000000000001');
        $orgaId = OrgaId::fromString('00000000-0000-0000-0000-000000000010');

        $this->expectException(NotFoundOrganizationException::class);

        /** @var UnjoinOrganizationService $service */
        $service = static::$container->get(UnjoinOrganizationService::class);
        $service->handle($orgaId, $memberId);
    }
}
