<?php

namespace App\Tests\End2End\Controller\MyFleet;

use App\Tests\End2End\WebTestCase;

class ImportFleetControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_import_ships_from_hangar_transfer_format_file(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
                INSERT INTO fleets(user_id, updated_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '2021-01-02T10:00:00Z');
                INSERT INTO ships(id, fleet_id, model, quantity)
                VALUES ('00000000-0000-0000-0000-000000000011', '00000000-0000-0000-0000-000000000001', 'Cyclone', 2);
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/my-fleet/import', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([
            'hangarExplorerContent' => file_get_contents(__DIR__.'/fixtures/import_hangar_explorer.json'),
        ]));

        static::assertSame(204, static::$client->getResponse()->getStatusCode());

        $result = static::$connection->executeQuery(<<<SQL
                SELECT * FROM ships WHERE fleet_id = '00000000-0000-0000-0000-000000000001';
            SQL
        )->fetchAllAssociative();
        static::assertArraySubset([
            [
                'model' => 'Cyclone',
                'quantity' => 3,
            ],
            [
                'model' => 'Cutlass 2949 Best In Show',
                'quantity' => 1,
            ],
        ], $result);
    }

    /**
     * @test
     */
    public function it_should_error_on_bad_json(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/my-fleet/import', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([
            'hangarExplorerContent' => '[{"foo":"bar",}]', // bad json
        ]));

        static::assertSame(400, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);
        static::assertSame('hangarExplorerContent', $json['violations']['violations'][0]['propertyPath']);
        static::assertSame('Unable to decode your file. Check it contains valid JSON.', $json['violations']['violations'][0]['title']);
    }

    /**
     * @test
     */
    public function it_should_return_error_if_not_logged(): void
    {
        static::$client->xmlHttpRequest('POST', '/api/my-fleet/import', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(401, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);
        static::assertSame(['message' => 'Authentication required.'], $json);
    }
}
