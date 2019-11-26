<?php

namespace App\Tests\Controller\MyFleet;

use App\Entity\Fleet;
use App\Entity\User;
use App\Service\Citizen\InfosProvider\CitizenInfosProviderInterface;
use App\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UploadControllerTest extends WebTestCase
{
    /** @var User */
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Ioni']);
    }

    /**
     * @group functional
     * @group api
     */
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
        $lastFleet = $this->user->getCitizen()->getLastFleet();
        $this->assertSame(2, $lastFleet->getVersion());
        $this->assertCount(2, $lastFleet->getShips());
        $this->assertSame('Cutlass Black', $lastFleet->getShips()[0]->getName());
        $this->assertSame('Cyclone', $lastFleet->getShips()[1]->getName());
    }

    /**
     * @group functional
     * @group api
     */
    public function testMustUploadFleetFileUpload(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/upload', [
            'fleetFile' => null,
        ]);

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('invalid_form', $json['error']);
        $this->assertArraySubset(['You must choose a JSON fleet file.'], $json['formErrors']);
    }
}
