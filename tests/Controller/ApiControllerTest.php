<?php

namespace App\Tests\Controller;

use App\Entity\Fleet;
use App\Entity\User;
use App\Service\CitizenInfosProviderInterface;
use App\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ApiControllerTest extends WebTestCase
{
    /** @var User */
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Ioni']);
    }

    public function testMe(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/me', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('d92e229e-e743-4583-905a-e02c57eacfe0', $json['id']);
        $this->assertSame('Ioni', $json['nickname']);
        $this->assertSame('2019-04-02T11:22:33+00:00', $json['createdAt']);
    }

    public function testValidUpload(): void
    {
        file_put_contents(sys_get_temp_dir().'/test-fleet.json', <<<EOT
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
        $citizenInfosProvider = static::$container->get(CitizenInfosProviderInterface::class);
        $citizenInfosProvider->setCitizen($this->user->getCitizen());

        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/upload', [], [
            'fleetFile' => new UploadedFile(sys_get_temp_dir().'/test-fleet.json', 'test-fleet.json', 'application/json', null),
        ]);

        $this->assertSame(204, $this->client->getResponse()->getStatusCode());

        /** @var Fleet $lastFleet */
        $lastFleet = $this->doctrine->getRepository(Fleet::class)->findOneBy(['owner' => $this->user->getCitizen()], ['version' => 'desc']);
        $this->assertSame(2, $lastFleet->getVersion());
        $this->assertCount(2, $lastFleet->getShips());
        $this->assertSame('Cutlass Black', $lastFleet->getShips()[0]->getName());
        $this->assertSame('Cyclone', $lastFleet->getShips()[1]->getName());
    }

    public function testMustUploadFleetFileUpload(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/upload', [
            'fleetFile' => null,
        ], []);
        $this->assertSame(400, $this->client->getResponse()->getStatusCode());

        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('invalid_form', $json['error']);
        $this->assertArraySubset(['You must upload a fleet file.'], $json['formErrors']);
    }

    public function testValidExport(): void
    {
        $jsonContent = <<<EOT
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
EOT;

        $citizenInfosProvider = static::$container->get(CitizenInfosProviderInterface::class);
        $citizenInfosProvider->setCitizen($this->user->getCitizen());

        $this->client->xmlHttpRequest('POST', '/api/export', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$this->user->getApiToken(),
            'CONTENT_TYPE' => 'application/json',
        ], $jsonContent);

        $this->assertSame(204, $this->client->getResponse()->getStatusCode());

        /** @var Fleet $lastFleet */
        $lastFleet = $this->doctrine->getRepository(Fleet::class)->findOneBy(['owner' => $this->user->getCitizen()], ['version' => 'desc']);
        $this->assertSame(2, $lastFleet->getVersion());
        $this->assertCount(2, $lastFleet->getShips());
        $this->assertSame('Cutlass Black', $lastFleet->getShips()[0]->getName());
        $this->assertSame('Cyclone', $lastFleet->getShips()[1]->getName());
    }

    public function testCreateCitizenFleetFile(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/create-citizen-fleet-file', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        /** @var BinaryFileResponse $response */
        $response = $this->client->getResponse();
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame('attachment; filename=citizen_fleet.json', $response->headers->get('Content-Disposition'));
    }

    public function testCreateOrgaFleetFile(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/create-organization-fleet-file/flk', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        /** @var BinaryFileResponse $response */
        $response = $this->client->getResponse();
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame('attachment; filename=organization_fleet.json', $response->headers->get('Content-Disposition'));
    }
}
