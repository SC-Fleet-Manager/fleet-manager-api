<?php

namespace App\Tests\Acceptance\MyOrganizations;

use App\Application\MyOrganizations\OrganizationsService;
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
        $output = $service->handle($memberId, 'https://example.org/api/organizations', 2);

        static::assertCount(2, $output->organizations);
        static::assertSame('https://example.org/api/organizations?sinceId=00000000-0000-0000-0000-000000000011', $output->nextUrl);

        static::assertSame('Force Coloniale Unifiée', $output->organizations[0]->name);
        static::assertSame('fcu', $output->organizations[0]->sid);
        static::assertTrue($output->organizations[0]->joined);

        static::assertSame('Fallkrom', $output->organizations[1]->name);
        static::assertSame('flk', $output->organizations[1]->sid);
        static::assertFalse($output->organizations[1]->joined);

        $output = $service->handle($memberId, 'https://example.org/api/organizations', 2, OrgaId::fromString('00000000-0000-0000-0000-000000000011'));

        static::assertCount(2, $output->organizations);
        static::assertSame('https://example.org/api/organizations?sinceId=00000000-0000-0000-0000-000000000013', $output->nextUrl);

        static::assertSame('Les Gardiens', $output->organizations[0]->name);
        static::assertSame('gardiens', $output->organizations[0]->sid);
        static::assertTrue($output->organizations[0]->joined);

        static::assertSame('Some orga 1', $output->organizations[1]->name);
        static::assertSame('some_orga_1', $output->organizations[1]->sid);
        static::assertFalse($output->organizations[1]->joined);

        $output = $service->handle($memberId, 'https://example.org/api/organizations', 2, OrgaId::fromString('00000000-0000-0000-0000-000000000013'));

        static::assertEmpty($output->organizations);
        static::assertNull($output->nextUrl);
    }
}
