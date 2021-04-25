<?php

namespace App\Tests\Acceptance\MyOrganizations;

use App\Application\MyOrganizations\JoinOrganizationService;
use App\Application\Provider\MemberProfileProviderInterface;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Exception\AlreadyMemberOfOrganizationException;
use App\Domain\Exception\NoMemberHandleException;
use App\Domain\Exception\NotFoundOrganizationException;
use App\Domain\MemberId;
use App\Domain\MemberProfile;
use App\Domain\OrgaId;
use App\Entity\Organization;
use App\Infrastructure\Provider\Organizations\InMemoryMemberProfileProvider;
use App\Infrastructure\Repository\Organization\InMemoryOrganizationRepository;
use App\Tests\Acceptance\KernelTestCase;

class JoinOrganizationServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_join_an_orga_of_logged_user(): void
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

        /** @var InMemoryMemberProfileProvider $memberProfileProvider */
        $memberProfileProvider = static::$container->get(MemberProfileProviderInterface::class);
        $memberProfileProvider->setProfiles([
            new MemberProfile($memberId, null, 'handle'),
        ]);

        /** @var JoinOrganizationService $service */
        $service = static::$container->get(JoinOrganizationService::class);
        $service->handle($orgaId, $memberId);

        $orga = $orgaRepository->getOrganization($orgaId);
        static::assertTrue($orga->isMemberOf($memberId));
        static::assertFalse($orga->hasJoined($memberId));
    }

    /**
     * @test
     */
    public function it_should_error_if_already_member_of_orga(): void
    {
        $memberId = MemberId::fromString('00000000-0000-0000-0000-000000000001');
        $orgaId = OrgaId::fromString('00000000-0000-0000-0000-000000000010');

        /** @var InMemoryOrganizationRepository $orgaRepository */
        $orgaRepository = static::$container->get(OrganizationRepositoryInterface::class);
        $orgaRepository->save(new Organization(
            $orgaId,
            $memberId, // already member
            'My orga',
            'org',
            null,
            new \DateTimeImmutable('2021-01-01T10:00:00Z'),
        ));

        /** @var InMemoryMemberProfileProvider $memberProfileProvider */
        $memberProfileProvider = static::$container->get(MemberProfileProviderInterface::class);
        $memberProfileProvider->setProfiles([
            new MemberProfile($memberId, null, 'handle'),
        ]);

        $this->expectException(AlreadyMemberOfOrganizationException::class);

        /** @var JoinOrganizationService $service */
        $service = static::$container->get(JoinOrganizationService::class);
        $service->handle($orgaId, $memberId);
    }

    /**
     * @test
     */
    public function it_should_error_if_logged_user_has_no_handle(): void
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

        /** @var InMemoryMemberProfileProvider $memberProfileProvider */
        $memberProfileProvider = static::$container->get(MemberProfileProviderInterface::class);
        $memberProfileProvider->setProfiles([
            new MemberProfile($memberId, null, null),
        ]);

        $this->expectException(NoMemberHandleException::class);

        /** @var JoinOrganizationService $service */
        $service = static::$container->get(JoinOrganizationService::class);
        $service->handle($orgaId, $memberId);
    }

    /**
     * @test
     */
    public function it_should_error_if_join_unexistent_orga(): void
    {
        $memberId = MemberId::fromString('00000000-0000-0000-0000-000000000001');
        $orgaId = OrgaId::fromString('00000000-0000-0000-0000-000000000010');

        $this->expectException(NotFoundOrganizationException::class);

        /** @var JoinOrganizationService $service */
        $service = static::$container->get(JoinOrganizationService::class);
        $service->handle($orgaId, $memberId);
    }
}
