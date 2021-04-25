<?php

namespace App\Tests\Acceptance\MyOrganizations;

use App\Application\MyOrganizations\OrganizationsService;
use App\Application\MyOrganizations\Output\OrganizationsCollectionOutput;
use App\Application\MyOrganizations\Output\OrganizationsItemOutput;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Entity\Organization;
use App\Infrastructure\Repository\Organization\InMemoryOrganizationRepository;
use App\Tests\Acceptance\KernelTestCase;

class OrganizationsServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_return_list_of_orgas_paginated(): void
    {
        $memberId = MemberId::fromString('00000000-0000-0000-0000-000000000001');

        /** @var InMemoryOrganizationRepository $orgaRepository */
        $orgaRepository = static::$container->get(OrganizationRepositoryInterface::class);

        $orgaRepository->save(new Organization(
            OrgaId::fromString('00000000-0000-0000-0000-000000000010'),
            $memberId,
            'Force Coloniale Unifiée',
            'fcu',
            'https://example.com/logo.png',
            new \DateTimeImmutable('2021-01-01T10:00:00Z')
        ));

        $orgaRepository->save($orga = new Organization(
            OrgaId::fromString('00000000-0000-0000-0000-000000000011'),
            MemberId::fromString('00000000-0000-0000-0000-000000000002'),
            'Fallkrom',
            'flk',
            null,
            new \DateTimeImmutable('2021-01-02T10:00:00Z')
        ));
        $orga->addMember($memberId, false, new \DateTimeImmutable('2021-01-01T11:00:00Z'));

        $orgaRepository->save($orga = new Organization(
            OrgaId::fromString('00000000-0000-0000-0000-000000000012'),
            MemberId::fromString('00000000-0000-0000-0000-000000000003'),
            'Les Gardiens',
            'gardiens',
            null,
            new \DateTimeImmutable('2021-01-03T10:00:00Z')
        ));
        $orga->addMember($memberId, true, new \DateTimeImmutable('2021-01-01T11:00:00Z'));

        $orgaRepository->save(new Organization(
            OrgaId::fromString('00000000-0000-0000-0000-000000000013'),
            MemberId::fromString('00000000-0000-0000-0000-000000000004'),
            'Some orga 1',
            'some_orga_1',
            null,
            new \DateTimeImmutable('2021-01-04T10:00:00Z')
        ));

        /** @var OrganizationsService $service */
        $service = static::$container->get(OrganizationsService::class);
        $output = $service->handle('https://example.org/api/organizations', 2);

        static::assertEquals(new OrganizationsCollectionOutput(
            [
                new OrganizationsItemOutput(
                    OrgaId::fromString('00000000-0000-0000-0000-000000000010'),
                    'Force Coloniale Unifiée',
                    'fcu',
                    'https://example.com/logo.png',
                ),
                new OrganizationsItemOutput(
                    OrgaId::fromString('00000000-0000-0000-0000-000000000011'),
                    'Fallkrom',
                    'flk',
                    null,
                ),
            ],
            'https://example.org/api/organizations?sinceId=00000000-0000-0000-0000-000000000011',
        ), $output);

        $output = $service->handle('https://example.org/api/organizations', 2, OrgaId::fromString('00000000-0000-0000-0000-000000000011'));

        static::assertEquals(new OrganizationsCollectionOutput(
            [
                new OrganizationsItemOutput(
                    OrgaId::fromString('00000000-0000-0000-0000-000000000012'),
                    'Les Gardiens',
                    'gardiens',
                    null,
                ),
                new OrganizationsItemOutput(
                    OrgaId::fromString('00000000-0000-0000-0000-000000000013'),
                    'Some orga 1',
                    'some_orga_1',
                    null,
                ),
            ],
            'https://example.org/api/organizations?sinceId=00000000-0000-0000-0000-000000000013',
        ), $output);

        $output = $service->handle('https://example.org/api/organizations', 2, OrgaId::fromString('00000000-0000-0000-0000-000000000013'));

        static::assertEmpty($output->organizations);
        static::assertNull($output->nextUrl);
    }

    /**
     * @test
     */
    public function it_should_return_a_filtered_list_of_orgas_with_search_param(): void
    {
        /** @var InMemoryOrganizationRepository $orgaRepository */
        $orgaRepository = static::$container->get(OrganizationRepositoryInterface::class);
        $orgaRepository->save(new Organization(
            OrgaId::fromString('00000000-0000-0000-0000-000000000010'),
            MemberId::fromString('00000000-0000-0000-0000-000000000001'),
            'Les bons gÄrdîEns',
            'les_bons',
            null,
            new \DateTimeImmutable('2021-01-01T10:00:00Z')
        ));
        $orgaRepository->save(new Organization(
            OrgaId::fromString('00000000-0000-0000-0000-000000000011'),
            MemberId::fromString('00000000-0000-0000-0000-000000000002'),
            'Les douteux',
            'gÂrDiens_douteu',
            null,
            new \DateTimeImmutable('2021-01-02T10:00:00Z')
        ));
        $orgaRepository->save(new Organization(
            OrgaId::fromString('00000000-0000-0000-0000-000000000012'),
            MemberId::fromString('00000000-0000-0000-0000-000000000003'),
            'Les videurs',
            'videurs',
            null,
            new \DateTimeImmutable('2021-01-03T10:00:00Z')
        ));
        $orgaRepository->save(new Organization(
            OrgaId::fromString('00000000-0000-0000-0000-000000000013'),
            MemberId::fromString('00000000-0000-0000-0000-000000000004'),
            'Derniers gardiens',
            'derniers',
            null,
            new \DateTimeImmutable('2021-01-04T10:00:00Z')
        ));

        /** @var OrganizationsService $service */
        $service = static::$container->get(OrganizationsService::class);
        $output = $service->handle('https://example.org/api/organizations', 2, searchQuery: 'Gardiens');

        static::assertEquals(new OrganizationsCollectionOutput(
            [
                new OrganizationsItemOutput(
                    OrgaId::fromString('00000000-0000-0000-0000-000000000010'),
                    'Les bons gÄrdîEns',
                    'les_bons',
                    null,
                ),
                new OrganizationsItemOutput(
                    OrgaId::fromString('00000000-0000-0000-0000-000000000011'),
                    'Les douteux',
                    'gÂrDiens_douteu',
                    null,
                ),
            ],
            'https://example.org/api/organizations?sinceId=00000000-0000-0000-0000-000000000011',
        ), $output);

        $output = $service->handle('https://example.org/api/organizations', 2, OrgaId::fromString('00000000-0000-0000-0000-000000000011'), searchQuery: 'Gardiens');

        static::assertEquals(new OrganizationsCollectionOutput(
            [
                new OrganizationsItemOutput(
                    OrgaId::fromString('00000000-0000-0000-0000-000000000013'),
                    'Derniers gardiens',
                    'derniers',
                    null,
                ),
            ],
            null,
        ), $output);
    }
}
