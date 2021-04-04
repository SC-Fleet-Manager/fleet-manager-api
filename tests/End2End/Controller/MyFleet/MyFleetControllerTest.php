<?php

namespace App\Tests\End2End\Controller\MyFleet;

use App\Tests\End2End\WebTestCase;
use Symfony\Component\Uid\Ulid;

class MyFleetControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_return_the_fleet_and_ships_of_logged_user(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
                INSERT INTO fleets(id, user_id, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000010', '00000000-0000-0000-0000-000000000001', '2021-01-02T10:00:00Z');
                INSERT INTO ships(id, fleet_id, name, image_url, quantity)
                VALUES ('00000000-0000-0000-0000-000000000020', '00000000-0000-0000-0000-000000000010', 'Avenger', null, 2),
                       ('00000000-0000-0000-0000-000000000021', '00000000-0000-0000-0000-000000000010', 'Mercury Star Runner', 'https://example.com/mercury.jpg', 10),
                       ('00000000-0000-0000-0000-000000000022', '00000000-0000-0000-0000-000000000010', 'Javelin', 'https://example.com/javelin.jpg', 1);
            SQL
        );

        static::$client->xmlHttpRequest('GET', '/api/my-fleet', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(200, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);
        static::assertSame([
            'id' => (string) Ulid::fromString('00000000-0000-0000-0000-000000000010'),
            'ships' => [
                'items' => [
                    [
                        'id' => (string) Ulid::fromString('00000000-0000-0000-0000-000000000020'),
                        'name' => 'Avenger',
                        'imageUrl' => null,
                        'quantity' => 2,
                    ],
                    [
                        'id' => (string) Ulid::fromString('00000000-0000-0000-0000-000000000021'),
                        'name' => 'Mercury Star Runner',
                        'imageUrl' => 'https://example.com/mercury.jpg',
                        'quantity' => 10,
                    ],
                    [
                        'id' => (string) Ulid::fromString('00000000-0000-0000-0000-000000000022'),
                        'name' => 'Javelin',
                        'imageUrl' => 'https://example.com/javelin.jpg',
                        'quantity' => 1,
                    ],
                ],
                'count' => 3,
            ],
            'updatedAt' => '2021-01-02T10:00:00+00:00',
        ], $json);
    }

    /**
     * @test
     */
    public function it_should_return_error_if_not_logged(): void
    {
        static::$client->xmlHttpRequest('GET', '/api/my-fleet', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(401, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);
        static::assertSame(['message' => 'Authentication required.'], $json);
    }
}
