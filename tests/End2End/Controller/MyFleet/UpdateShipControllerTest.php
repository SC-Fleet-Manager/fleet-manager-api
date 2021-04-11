<?php

namespace App\Tests\End2End\Controller\MyFleet;

use App\Tests\End2End\WebTestCase;

class UpdateShipControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_update_a_ship_for_logged_user(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
                INSERT INTO fleets(user_id, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '2021-01-02T10:00:00Z');
                INSERT INTO ships(id, fleet_id, model, quantity)
                VALUES ('00000000-0000-0000-0000-000000000011', '00000000-0000-0000-0000-000000000001', 'Avenger', 2);
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/my-fleet/update-ship/00000000-0000-0000-0000-000000000011', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([
            'model' => 'Avenger 2',
            'pictureUrl' => 'https://starcitizen.tools/avenger.jpg',
            'quantity' => 5,
        ]));

        static::assertSame(204, static::$client->getResponse()->getStatusCode());

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM ships WHERE id = '00000000-0000-0000-0000-000000000011';
            SQL
        )->fetchAssociative();
        static::assertArraySubset([
            'model' => 'Avenger 2',
            'image_url' => 'https://starcitizen.tools/avenger.jpg',
            'quantity' => 5,
        ], $result);
    }

    /**
     * @test
     */
    public function it_should_update_a_ship_without_changing_model(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
                INSERT INTO fleets(user_id, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '2021-01-02T10:00:00Z');
                INSERT INTO ships(id, fleet_id, model, quantity)
                VALUES ('00000000-0000-0000-0000-000000000011', '00000000-0000-0000-0000-000000000001', 'Avenger', 2);
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/my-fleet/update-ship/00000000-0000-0000-0000-000000000011', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([
            'model' => 'Avenger',
            'quantity' => 3,
        ]));

        static::assertSame(204, static::$client->getResponse()->getStatusCode());

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM ships WHERE id = '00000000-0000-0000-0000-000000000011';
            SQL
        )->fetchAssociative();
        static::assertArraySubset([
            'model' => 'Avenger',
            'quantity' => 3,
        ], $result);
    }

    /**
     * @test
     */
    public function it_should_return_error_if_not_logged(): void
    {
        static::$client->xmlHttpRequest('POST', '/api/my-fleet/update-ship/00000000-0000-0000-0000-000000000011', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(401, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);
        static::assertSame(['message' => 'Authentication required.'], $json);
    }
}
