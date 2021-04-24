<?php

namespace App\Tests\Acceptance\MyOrganizations;

use App\Application\MyOrganizations\OrganizationCandidatesService;
use App\Application\Provider\MemberProfileProviderInterface;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Exception\NotFounderOfOrganizationException;
use App\Domain\MemberId;
use App\Domain\MemberProfile;
use App\Domain\OrgaId;
use App\Entity\Organization;
use App\Infrastructure\Provider\Organizations\InMemoryMemberProfileProvider;
use App\Infrastructure\Repository\Organization\InMemoryOrganizationRepository;
use App\Tests\Acceptance\KernelTestCase;

class OrganizationCandidatesServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_return_list_of_candidates_of_an_orga_for_logged_founder(): void
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

        /** @var InMemoryMemberProfileProvider $memberProfileProvider */
        $memberProfileProvider = static::$container->get(MemberProfileProviderInterface::class);
        $memberProfileProvider->setProfiles([
            new MemberProfile($memberId, 'Ioni'),
        ]);

        /** @var OrganizationCandidatesService $service */
        $service = static::$container->get(OrganizationCandidatesService::class);
        $output = $service->handle($orgaId, $founderId);

        static::assertCount(1, $output->candidates);
        static::assertSame('Ioni', $output->candidates[0]->nickname);
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

        /** @var OrganizationCandidatesService $service */
        $service = static::$container->get(OrganizationCandidatesService::class);
        $service->handle($orgaId, $memberId);
    }
}
