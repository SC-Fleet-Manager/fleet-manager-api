<?php

namespace App\Tests\End2End\Controller\MyFleet;

use App\Domain\Event\UpdatedFleetEvent;
use App\Tests\End2End\WebTestCase;
use Symfony\Component\Messenger\Stamp\BusNameStamp;

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

        static::$client->xmlHttpRequest('POST', '/api/my-fleet/create-ship', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([
            'model' => 'Avenger',
            'pictureUrl' => 'https://starcitizen.tools/avenger.jpg',
            'quantity' => 3,
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
            'model' => 'Avenger',
            'image_url' => 'https://starcitizen.tools/avenger.jpg',
            'quantity' => 3,
        ], $result);

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM messenger_messages;
            SQL
        )->fetchAllAssociative();
        static::assertArraySubset([
            [
                'queue_name' => 'organizations_events',
                'body' => '{"ownerId":"00000000-0000-0000-0000-000000000001","ships":[{"model":"Avenger","logoUrl":"https:\/\/starcitizen.tools\/avenger.jpg","quantity":3}],"version":1}',
                'headers' => json_encode([
                    'type' => UpdatedFleetEvent::class,
                    'X-Message-Stamp-'.BusNameStamp::class => '[{"busName":"event.bus"}]',
                    'Content-Type' => 'application/json',
                ]),
            ],
        ], $result);
    }

    /**
     * @test
     */
    public function it_should_error_with_no_models(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/my-fleet/create-ship', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([]));

        static::assertSame(400, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);

        static::assertSame('invalid_form', $json['error']);
        static::assertSame('model', $json['violations']['violations'][0]['propertyPath']);
        static::assertSame('This value should not be blank.', $json['violations']['violations'][0]['title']);
    }

    /**
     * @test
     */
    public function it_should_add_a_ship_with_a_duplicate_model(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
                INSERT INTO fleets(user_id, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '2021-01-02T10:00:00Z');
                INSERT INTO ships(id, fleet_id, model, image_url, quantity)
                VALUES ('00000000-0000-0000-0000-000000000010', '00000000-0000-0000-0000-000000000001', 'Avenger', 'https://starcitizen.tools/avenger.jpg', 2);
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/my-fleet/create-ship', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([
            'model' => ' -Âvënger,',
        ]));

        static::assertSame(204, static::$client->getResponse()->getStatusCode());

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM ships WHERE fleet_id = '00000000-0000-0000-0000-000000000001' ORDER BY quantity ASC;
            SQL
        )->fetchAllAssociative();
        static::assertNotFalse($result, 'The ship should be created.');
        static::assertArraySubset([
            [
                'model' => '-Âvënger,',
                'image_url' => null,
                'quantity' => 1,
            ],
            [
                'model' => 'Avenger',
                'image_url' => 'https://starcitizen.tools/avenger.jpg',
                'quantity' => 2,
            ],
        ], $result);
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
                INSERT INTO ships(id, fleet_id, model)
                VALUES $shipsSql;
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/my-fleet/create-ship', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([
            'model' => 'Mercury',
        ]));

        static::assertSame(400, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);

        static::assertSame('invalid_form', $json['error']);
        static::assertSame('You have reached the limit of 300 ships.', $json['violations']['violations'][0]['title']);
    }

    /**
     * @test
     */
    public function it_should_set_quantity_1_minimum(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
                INSERT INTO fleets(user_id, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '2021-01-02T10:00:00Z');
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/my-fleet/create-ship', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([
            'model' => 'Mercury',
            'quantity' => -5,
        ]));

        static::assertSame(204, static::$client->getResponse()->getStatusCode());

        $result = static::$connection->executeQuery(<<<SQL
                SELECT quantity FROM ships WHERE fleet_id = '00000000-0000-0000-0000-000000000001';
            SQL
        )->fetchAssociative();
        static::assertNotFalse($result, 'The ship should be created.');
        static::assertEquals(1, $result['quantity']);
    }

    /**
     * @test
     */
    public function it_should_return_error_if_not_logged(): void
    {
        static::$client->xmlHttpRequest('POST', '/api/my-fleet/create-ship', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(401, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);
        static::assertSame(['message' => 'Authentication required.'], $json);
    }
}
