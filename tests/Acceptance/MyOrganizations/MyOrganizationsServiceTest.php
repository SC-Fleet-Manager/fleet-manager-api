<?php

namespace App\Tests\Acceptance\MyOrganizations;

use App\Application\MyOrganizations\MyOrganizationsService;
use App\Application\Repository\OrganizationRepositoryInterface;
use App\Domain\MemberId;
use App\Domain\OrgaId;
use App\Entity\Organization;
use App\Infrastructure\Repository\Organization\InMemoryOrganizationRepository;
use App\Tests\Acceptance\KernelTestCase;

class MyOrganizationsServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_return_list_of_orga_of_logged_user(): void
    {
        $memberId = MemberId::fromString('00000000-0000-0000-0000-000000000001');
        $founderId = MemberId::fromString('00000000-0000-0000-0000-000000000002');

        /** @var InMemoryOrganizationRepository $orgaRepository */
        $orgaRepository = static::$container->get(OrganizationRepositoryInterface::class);

        $orga = new Organization(
            OrgaId::fromString('00000000-0000-0000-0000-000000000010'),
            $founderId,
            'Force Coloniale Unifiée',
            'fcu',
            'https://example.com/logo.png',
            new \DateTimeImmutable('2021-01-01T10:00:00Z')
        );
        $orga->addMember($memberId, false, new \DateTimeImmutable('2021-01-01T11:00:00Z'));
        $orgaRepository->save($orga);
        $orgaRepository->save(new Organization(
            OrgaId::fromString('00000000-0000-0000-0000-000000000011'),
            $founderId,
            'Fallkrom',
            'flk',
            null,
            new \DateTimeImmutable('2021-01-02T10:00:00Z')
        ));
        $orgaRepository->save(new Organization(
            OrgaId::fromString('00000000-0000-0000-0000-000000000012'),
            $memberId,
            'Les Gardiens',
            'gardiens',
            null,
            new \DateTimeImmutable('2021-01-03T10:00:00Z')
        ));

        /** @var MyOrganizationsService $service */
        $service = static::$container->get(MyOrganizationsService::class);
        $output = $service->handle($memberId);

        static::assertCount(2, $output->organizations);

        static::assertSame('Force Coloniale Unifiée', $output->organizations[0]->name);
        static::assertSame('fcu', $output->organizations[0]->sid);
        static::assertSame('https://example.com/logo.png', $output->organizations[0]->logoUrl);
        static::assertFalse($output->organizations[0]->joined);

        static::assertSame('Les Gardiens', $output->organizations[1]->name);
        static::assertSame('gardiens', $output->organizations[1]->sid);
        static::assertNull($output->organizations[1]->logoUrl);
        static::assertTrue($output->organizations[1]->joined);
    }
}
