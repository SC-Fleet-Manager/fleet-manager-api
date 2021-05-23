<?php

namespace App\Tests\End2End\Controller\MyFleet;

use App\Domain\Event\UpdatedFleetEvent;
use App\Tests\End2End\WebTestCase;
use Symfony\Component\Messenger\Stamp\BusNameStamp;

class DeleteShipControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_delete_a_ship_of_logged_user(): void
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

        static::xhr('POST', '/api/my-fleet/delete-ship/00000000-0000-0000-0000-000000000011', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(204, static::$client->getResponse()->getStatusCode());

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM ships WHERE id = '00000000-0000-0000-0000-000000000011';
            SQL
        )->fetchAssociative();
        static::assertFalse($result, 'The ship should be deleted.');

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM messenger_messages;
            SQL
        )->fetchAllAssociative();
        static::assertArraySubset([
            [
                'queue_name' => 'organizations_events',
                'body' => '{"ownerId":"00000000-0000-0000-0000-000000000001","ships":[],"version":1}',
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
    public function it_should_error_logged_user_has_no_fleet(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
            SQL
        );

        static::xhr('POST', '/api/my-fleet/delete-ship/00000000-0000-0000-0000-000000000011', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(400, static::$client->getResponse()->getStatusCode());
        $json = static::json();

        static::assertSame([
            'error' => 'not_found_fleet',
            'errorMessage' => 'This user has no fleet. Please try to create a ship.',
            'userId' => '00000000-0000-0000-0000-000000000001',
        ], $json);
    }

    /**
     * @test
     */
    public function it_should_return_error_if_not_logged(): void
    {
        static::xhr('POST', '/api/my-fleet/delete-ship/00000000-0000-0000-0000-000000000011', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(401, static::$client->getResponse()->getStatusCode());
        $json = static::json();
        static::assertSame(['message' => 'Authentication required.'], $json);
    }
}
