<?php

namespace App\Tests\Integration\Repository\OrganizationRepository;

use App\Entity\Organization;
use App\Infrastructure\Repository\Organization\DoctrineOrganizationRepository;
use App\Tests\Integration\KernelTestCase;

class GetOrganizationsTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_return_paginated_filtered_list_of_organizations(): void
    {
        $orgaValues = '';
        for ($i = 0 + 100; $i < 201 + 100; ++$i) { // more than 200 (internal batch)
            $orgaValues .= "('00000000-0000-0000-0000-000000000$i', '00000000-0000-0000-0000-000000000002', 'An orga $i', 'fcu$i', '1${i}-01-01T10:00:00Z'),";
        }
        $orgaValues = rtrim($orgaValues, ',');
        static::$connection->executeStatement(<<<SQL
                INSERT INTO organizations(id, founder_id, name, sid, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000801', '00000000-0000-0000-0000-000000000003', 'Les bons gÄrdîEnß!', 'LESBONS', '2021-01-01T10:00:00Z'),
                       $orgaValues,
                       ('00000000-0000-0000-0000-000000000802', '00000000-0000-0000-0000-000000000003', 'Les douteux', 'GARDIENSSDOUTE', '2021-01-02T10:00:00Z'),
                       ('00000000-0000-0000-0000-000000000803', '00000000-0000-0000-0000-000000000004', 'Les gardienss basiques', 'BASIQUES', '2021-01-03T10:00:00Z');
            SQL
        );

        /** @var DoctrineOrganizationRepository $repository */
        $repository = static::$container->get(DoctrineOrganizationRepository::class);
        /** @var Organization[] $orgas */
        $orgas = $repository->getOrganizations(2, searchQuery: 'Gardienss');

        static::assertCount(2, $orgas);
        static::assertSame('00000000-0000-0000-0000-000000000803', (string) $orgas[0]->getId());
        static::assertSame('00000000-0000-0000-0000-000000000802', (string) $orgas[1]->getId());
    }
}
