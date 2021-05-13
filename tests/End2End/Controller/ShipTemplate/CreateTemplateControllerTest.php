<?php

namespace App\Tests\End2End\Controller\ShipTemplate;

use App\Tests\End2End\WebTestCase;

class CreateTemplateControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_create_a_ship_template_with_full_infos(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/ship-template/create', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([
            'model' => 'Cutlass Black',
            'pictureUrl' => 'https://starcitizen.tools/cutlass_black.jpg',
            'chassis' => [
                'name' => 'Cutlass',
            ],
            'manufacturer' => [
                'name' => 'Drake Interplanetary',
                'code' => 'DRAK',
            ],
            'size' => 'small',
            'role' => 'Transport',
            'cargoCapacity' => 12,
            'crew' => [
                'min' => 1,
                'max' => 3,
            ],
            'price' => [
                'pledge' => 1000,
                'inGame' => 2_000_000_000,
            ],
        ]));

        static::assertSame(204, static::$client->getResponse()->getStatusCode());

        $result = static::$connection->executeQuery('SELECT * FROM ship_templates;')->fetchAssociative();
        static::assertNotFalse($result, 'The template should be created.');
        static::assertArraySubset([
            'author_id' => '00000000-0000-0000-0000-000000000001',
            'model' => 'Cutlass Black',
            'image_url' => 'https://starcitizen.tools/cutlass_black.jpg',
            'version' => 1,
            'chassis_name' => 'Cutlass',
            'manufacturer_name' => 'Drake Interplanetary',
            'manufacturer_code' => 'DRAK',
            'ship_size_size' => 'small',
            'ship_role_role' => 'Transport',
            'cargo_capacity_capacity' => 12,
            'crew_min' => '1',
            'crew_max' => '3',
            'price_pledge' => '1000.00',
            'price_ingame' => '2000000000.00',
        ], $result);
    }

    /**
     * @test
     */
    public function it_should_create_a_ship_template_with_lowest_infos(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/ship-template/create', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([
            'model' => 'Cutlass Black',
            'chassis' => [
                'name' => 'Cutlass',
            ],
        ]));

        static::assertSame(204, static::$client->getResponse()->getStatusCode());

        $result = static::$connection->executeQuery('SELECT * FROM ship_templates;')->fetchAssociative();
        static::assertNotFalse($result, 'The template should be created.');
        static::assertArraySubset([
            'author_id' => '00000000-0000-0000-0000-000000000001',
            'model' => 'Cutlass Black',
            'image_url' => null,
            'version' => 1,
            'chassis_name' => 'Cutlass',
            'manufacturer_name' => null,
            'manufacturer_code' => null,
            'ship_size_size' => null,
            'ship_role_role' => null,
            'cargo_capacity_capacity' => 0,
            'crew_min' => null,
            'crew_max' => null,
            'price_pledge' => null,
            'price_ingame' => null,
        ], $result);
    }

    /**
     * @test
     */
    public function it_should_error_with_bad_values(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', '2021-01-01T10:00:00Z');
            SQL
        );

        static::$client->xmlHttpRequest('POST', '/api/ship-template/create', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ], json_encode([
            'model' => 'A',
            'pictureUrl' => 'https://example.com/a.jpg',
            'chassis' => [
                'name' => null,
            ],
            'manufacturer' => [
                'name' => 'AA',
                'code' => 'ABDEFG',
            ],
            'size' => 'foobar',
            'role' => str_repeat('a', 31),
            'crew' => [
                'min' => 2,
                'max' => 1,
            ],
            'price' => [
                'pledge' => 2_000_000_001,
            ],
        ]));

        static::assertSame(400, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);

        static::assertSame('invalid_form', $json['error']);
        static::assertSame('model', $json['violations']['violations'][0]['propertyPath']);
        static::assertSame('This value is too short. It should have 2 characters or more.', $json['violations']['violations'][0]['title']);
        static::assertSame('pictureUrl', $json['violations']['violations'][1]['propertyPath']);
        static::assertSame('The picture URL must come from robertsspaceindustries.com or starcitizen.tools.', $json['violations']['violations'][1]['title']);
        static::assertSame('chassis.name', $json['violations']['violations'][2]['propertyPath']);
        static::assertSame('This value should not be blank.', $json['violations']['violations'][2]['title']);
        static::assertSame('manufacturer.name', $json['violations']['violations'][3]['propertyPath']);
        static::assertSame('This value is too short. It should have 3 characters or more.', $json['violations']['violations'][3]['title']);
        static::assertSame('manufacturer.code', $json['violations']['violations'][4]['propertyPath']);
        static::assertSame('Manufacturer code must contain only 3 to 5 letters.', $json['violations']['violations'][4]['title']);
        static::assertSame('size', $json['violations']['violations'][5]['propertyPath']);
        static::assertSame('The value you selected is not a valid choice.', $json['violations']['violations'][5]['title']);
        static::assertSame('role', $json['violations']['violations'][6]['propertyPath']);
        static::assertSame('This value is too long. It should have 30 characters or less.', $json['violations']['violations'][6]['title']);
        static::assertSame('crew', $json['violations']['violations'][7]['propertyPath']);
        static::assertSame('Max crew must be greater than or equal to min.', $json['violations']['violations'][7]['title']);
        static::assertSame('price.pledge', $json['violations']['violations'][8]['propertyPath']);
        static::assertSame('This value should be between 0 and 2000000000.', $json['violations']['violations'][8]['title']);
    }
}
