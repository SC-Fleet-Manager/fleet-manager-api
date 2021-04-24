<?php

namespace App\Tests\Acceptance\MyOrganizations;

use App\Application\MyOrganizations\DeclineCandidateService;
use App\Application\MyOrganizations\OrganizationCandidatesService;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Exception\NotFounderOfOrganizationException;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Entity\Organization;
use App\Infrastructure\Repository\Organization\InMemoryOrganizationRepository;
use App\Tests\Acceptance\KernelTestCase;

class DeclineCandidateServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_decline_candidate_of_an_organization(): void
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

        /** @var DeclineCandidateService $service */
        $service = static::$container->get(DeclineCandidateService::class);
        $service->handle($orgaId, $founderId, $memberId);

        $orga = $orgaRepository->getOrganization($orgaId);
        static::assertFalse($orga->isMemberOf($memberId), 'Candidate should not be member of organization anymore.');
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

        /** @var DeclineCandidateService $service */
        $service = static::$container->get(DeclineCandidateService::class);
        $service->handle($orgaId, $memberId, $memberId);
    }
}
