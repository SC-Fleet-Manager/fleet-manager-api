<?php

namespace App\Tests\End2End\Controller\MyFleet;

use App\Tests\End2End\WebTestCase;

class CreateShipControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_create_a_ship_for_logged_user(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/create-ship', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([
            'name' => 'Avenger',
            'pictureUrl' => 'https://starcitizen.tools/avenger.jpg',
        ]));

        static::assertSame(204, static::$client->getResponse()->getStatusCode());

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM fleets WHERE user_id = '00000000-0000-0000-0000-000000000001';
            SQL
        )->fetchAssociative();
        static::assertNotFalse($result, 'The fleet should be created.');
        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM ships WHERE fleet_id = '00000000-0000-0000-0000-000000000001';
            SQL
        )->fetchAssociative();
        static::assertNotFalse($result, 'The ship should be created.');
        static::assertArraySubset([
            'name' => 'Avenger',
            'image_url' => 'https://starcitizen.tools/avenger.jpg',
            'quantity' => 1,
        ], $result);
    }

    /**
     * @test
     */
    public function it_should_error_with_no_names(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/create-ship', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([]));

        static::assertSame(400, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);

        static::assertSame('invalid_form', $json['error']);
        static::assertSame('name', $json['formErrors']['violations'][0]['propertyPath']);
        static::assertSame('This value should not be blank.', $json['formErrors']['violations'][0]['title']);
    }

    /**
     * @test
     */
    public function it_should_error_if_ship_name_already_exist_for_the_logged_user(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
                INSERT INTO fleets(user_id, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '2021-01-02T10:00:00Z');
                INSERT INTO ships(id, fleet_id, name, image_url, quantity)
                VALUES ('00000000-0000-0000-0000-000000000010', '00000000-0000-0000-0000-000000000001', 'Avenger', null, 2);
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/create-ship', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([
            'name' => ' -Âvënger,',
        ]));

        static::assertSame(400, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);

        static::assertSame('invalid_form', $json['error']);
        static::assertSame('name', $json['formErrors']['violations'][0]['propertyPath']);
        static::assertSame('You have already a ship with this name.', $json['formErrors']['violations'][0]['title']);
    }

    /**
     * @test
     */
    public function it_should_error_if_there_are_already_300_ships_for_the_logged_user(): void
    {
        $shipsSql = '';
        foreach (range(101, 400) as $i) {
            $shipsSql .= "('00000000-0000-0000-0000-000000000$i', '00000000-0000-0000-0000-000000000001', 'Avenger $i'),";
        }
        $shipsSql = rtrim($shipsSql, ',');
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
                INSERT INTO fleets(user_id, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '2021-01-02T10:00:00Z');
                INSERT INTO ships(id, fleet_id, name)
                VALUES $shipsSql;
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/create-ship', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([
            'name' => 'Mercury',
        ]));

        static::assertSame(400, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);

        static::assertSame('invalid_form', $json['error']);
        static::assertSame('You have reached the limit of 300 ships.', $json['formErrors']['violations'][0]['title']);
    }

    /**
     * @test
     */
    public function it_should_return_error_if_not_logged(): void
    {
        static::$client->xmlHttpRequest('POST', '/api/create-ship', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(401, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);
        static::assertSame(['message' => 'Authentication required.'], $json);
    }
}
