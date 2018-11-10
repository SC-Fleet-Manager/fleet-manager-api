<?php

declare(strict_types=1);

namespace App\Tests\Infrastructure\Controller;

use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ApiControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    protected static function createClient(array $options = array(), array $server = array())
    {
        static::bootKernel($options);

        $client = static::$kernel->getContainer()->get('test.client');
        $client->setServerParameters($server);

        return $client;
    }

    public function testValidUpload(): void
    {
        file_put_contents('/tmp/test-fleet.json', <<<EOT
[
  {
    "manufacturer": "Drake",
    "name": "Cutlass Black",
    "lti": true,
    "warbond": true,
    "package_id": "15109407",
    "pledge": "Package - Origin 100i Starter Game Package Warbond",
    "pledge_date": "April 28, 2018",
    "cost": "$110.00 USD"
  },
  {
    "manufacturer": "Tumbril",
    "name": "Cyclone",
    "lti": false,
    "warbond": false,
    "package_id": "15186605",
    "pledge": "Standalone Ship - Tumbril Cyclone ",
    "pledge_date": "May 15, 2018",
    "cost": "$55.00 USD"
  }
]
EOT
);
        $client = static::createClient();
        $client->request('POST', '/upload', [
            'handleSC' => 'not-yet-persisted',
        ], [
            'fleetFile' => new UploadedFile('/tmp/test-fleet.json', 'test-fleet.json', 'application/json', null),
        ]);
        $this->assertSame(204, $client->getResponse()->getStatusCode());
    }

    public function testMustUploadFleetFileUpload(): void
    {
        $client = static::createClient();
        $client->request('POST', '/upload', [
            'handleSC' => 'ionni',
        ], []); // no file
        $this->assertSame(400, $client->getResponse()->getStatusCode());

        $json = \json_decode($client->getResponse()->getContent(), true);
        $this->assertSame('invalid_form', $json['error']);
        $this->assertArraySubset(['You must upload a fleet file.'], $json['formErrors']);
    }
}
