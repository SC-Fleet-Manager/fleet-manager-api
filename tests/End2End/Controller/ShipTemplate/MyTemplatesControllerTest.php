<?php

namespace App\Tests\End2End\Controller\ShipTemplate;

use App\Tests\End2End\WebTestCase;

class MyTemplatesControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_return_collection_of_ship_templates_of_the_logged_user(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
                INSERT INTO ship_templates(id, author_id, model, updated_at, chassis_name, cargo_capacity_capacity)
                VALUES ('00000000-0000-0000-0000-000000000011', '00000000-0000-0000-0000-000000000001', 'Aurora MR', '2021-01-02T10:00:00Z', 'Aurora', 0),
                       ('00000000-0000-0000-0000-000000000012', '00000000-0000-0000-0000-000000000002', 'Aurora MR', '2021-01-03T10:00:00Z', 'Aurora', 0);
                INSERT INTO ship_templates(id, author_id, model, image_url, updated_at, version, chassis_name, manufacturer_name, manufacturer_code, ship_size_size, ship_role_role, cargo_capacity_capacity, crew_min, crew_max, price_pledge, price_ingame)
                VALUES ('00000000-0000-0000-0000-000000000010', '00000000-0000-0000-0000-000000000001', 'Avenger Titan', 'https://example.org/avenger.jpg', '2021-01-01T10:00:00Z', 1, 'Avenger', 'Robert Space Industries', 'RSI', 'small', 'Combat', 11, 1, 3, 5000, 2000000);
            SQL
        );

        static::$client->xmlHttpRequest('GET', '/api/my-ship-templates', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(200, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);
        static::assertSame([
            'items' => [
                [
                    'id' => '00000000-0000-0000-0000-000000000011',
                    'model' => 'Aurora MR',
                    'pictureUrl' => null,
                    'shipChassis' => [
                        'name' => 'Aurora',
                    ],
                    'manufacturer' => [
                        'name' => null,
                        'code' => null,
                    ],
                    'size' => null,
                    'role' => null,
                    'cargoCapacity' => [
                        'capacity' => 0,
                    ],
                    'crew' => [
                        'min' => null,
                        'max' => null,
                    ],
                    'price' => [
                        'pledge' => null,
                        'ingame' => null,
                    ],
                ],
                [
                    'id' => '00000000-0000-0000-0000-000000000010',
                    'model' => 'Avenger Titan',
                    'pictureUrl' => 'https://example.org/avenger.jpg',
                    'shipChassis' => [
                        'name' => 'Avenger',
                    ],
                    'manufacturer' => [
                        'name' => 'Robert Space Industries',
                        'code' => 'RSI',
                    ],
                    'size' => 'small',
                    'role' => 'Combat',
                    'cargoCapacity' => [
                        'capacity' => 11,
                    ],
                    'crew' => [
                        'min' => 1,
                        'max' => 3,
                    ],
                    'price' => [
                        'pledge' => 5000,
                        'ingame' => 2_000_000,
                    ],
                ],
            ],
        ], $json);
    }
}
