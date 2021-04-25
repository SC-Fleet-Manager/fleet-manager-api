<?php

namespace App\Tests\Acceptance\MyOrganizations;

use App\Application\MyOrganizations\UpdateOrganizationService;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\Exception\NotFounderOfOrganizationException;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Entity\Organization;
use App\Infrastructure\Repository\Organization\InMemoryOrganizationRepository;
use App\Tests\Acceptance\KernelTestCase;

class UpdateOrganizationServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_update_an_orga(): void
    {
        $founderId = MemberId::fromString('00000000-0000-0000-0000-000000000001');
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

        /** @var UpdateOrganizationService $service */
        $service = static::$container->get(UpdateOrganizationService::class);
        $service->handle($orgaId, $founderId, 'Force Coloniale Unifiée', 'https://example.org/picture.jpg');

        $orga = $orgaRepository->getOrganization($orgaId);
        static::assertSame('Force Coloniale Unifiée', $orga->getName());
        static::assertSame('https://example.org/picture.jpg', $orga->getLogoUrl());
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

        $this->expectException(NotFounderOfOrganizationException::class);

        /** @var UpdateOrganizationService $service */
        $service = static::$container->get(UpdateOrganizationService::class);
        $service->handle($orgaId, $memberId, 'Force Coloniale Unifiée', 'https://example.org/picture.jpg');
    }
}
