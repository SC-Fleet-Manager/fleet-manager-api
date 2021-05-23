<?php

namespace App\Tests\End2End\Controller\MyOrganizations;

use App\Tests\End2End\WebTestCase;

class OrganizationsItemControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_return_infos_and_fleet_of_an_organization(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
                INSERT INTO organizations(id, founder_id, name, sid, logo_url, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000010', '00000000-0000-0000-0000-000000000001', 'An orga 1', 'FCU1', 'https://example.org/logo.png', '2021-01-01T10:00:00Z');
                INSERT INTO memberships(member_id, organization_id, joined)
                VALUES ('00000000-0000-0000-0000-000000000001', '00000000-0000-0000-0000-000000000010', true);
                INSERT INTO organization_fleets(orga_id, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000010', '2021-01-02T10:00:00Z');
                INSERT INTO organization_ships(id, organization_fleet_id, model, image_url, quantity)
                VALUES ('00000000-0000-0000-0000-000000000020', '00000000-0000-0000-0000-000000000010', 'Avenger', 'https://example.org/avenger.jpg', 5),
                       ('00000000-0000-0000-0000-000000000021', '00000000-0000-0000-0000-000000000010', 'Mercury Star Runner', null, 2);
            SQL
        );

        static::xhr('GET', '/api/organizations/00000000-0000-0000-0000-000000000010', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(200, static::$client->getResponse()->getStatusCode());
        $json = static::json();

        static::assertSame([
            'id' => '00000000-0000-0000-0000-000000000010',
            'name' => 'An orga 1',
            'sid' => 'FCU1',
            'logoUrl' => 'https://example.org/logo.png',
            'founder' => true,
            'fleet' => [
                'ships' => [
                    [
                        'model' => 'Avenger',
                        'imageUrl' => 'https://example.org/avenger.jpg',
                        'quantity' => 5,
                    ],
                    [
                        'model' => 'Mercury Star Runner',
                        'imageUrl' => null,
                        'quantity' => 2,
                    ],
                ],
            ],
        ], $json);
    }

    /**
     * @test
     */
    public function it_should_return_error_if_not_logged(): void
    {
        static::xhr('GET', '/api/organizations/00000000-0000-0000-0000-000000000010', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(401, static::$client->getResponse()->getStatusCode());
        $json = static::json();
        static::assertSame(['message' => 'Authentication required.'], $json);
    }
}
