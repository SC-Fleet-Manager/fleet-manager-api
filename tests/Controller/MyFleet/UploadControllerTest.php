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
                  },
                  {
                    "manufacturer": "Tumbril",
                    "name": "Cyclone",
                    "lti": false,
                    "monthsInsurance": 6,
                    "pledge_date": "May 15, 2018"
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
        $this->assertCount(3, $lastFleet->getShips());
        $this->assertSame('Cutlass Black', $lastFleet->getShips()[0]->getName());
        $this->assertSame('Cyclone', $lastFleet->getShips()[1]->getName());
        $this->assertFalse($lastFleet->getShips()[1]->isInsured());
        $this->assertNull($lastFleet->getShips()[1]->getInsuranceDuration());
        $this->assertSame(6, $lastFleet->getShips()[2]->getInsuranceDuration());
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
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('invalid_form', $json['error']);
        $this->assertArraySubset(['You must choose a JSON fleet file.'], $json['formErrors']);
    }

    /**
     * @group functional
     * @group api
     */
    public function testMissingOptionalFields(): void
    {
        file_put_contents(sys_get_temp_dir().'/test-fleet.json', <<<EOT
                [
                  {
                    "manufacturer": "Drake",
                    "name": "Cutlass Black",
                    "pledge_date": "April 28, 2018"
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
        $this->assertCount(1, $lastFleet->getShips());
        $this->assertSame('Cutlass Black', $lastFleet->getShips()[0]->getName());
        $this->assertFalse($lastFleet->getShips()[0]->isInsured());
        $this->assertNull($lastFleet->getShips()[0]->getCost());
    }

    /**
     * @group functional
     * @group api
     */
    public function testDifferentCosts(): void
    {
        file_put_contents(sys_get_temp_dir().'/test-fleet.json', <<<EOT
                [
                  {
                    "manufacturer": "Drake",
                    "name": "Cutlass Black",
                    "cost": "$1,000.00",
                    "pledge_date": "April 28, 2018"
                  },
                  {
                    "manufacturer": "Drake",
                    "name": "Cutlass Black",
                    "cost": "$500.00",
                    "pledge_date": "April 28, 2018"
                  },
                  {
                    "manufacturer": "Drake",
                    "name": "Cutlass Black",
                    "cost": "$2,123,456.78",
                    "pledge_date": "April 28, 2018"
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
        $this->assertSame(1000.0, $lastFleet->getShips()[0]->getCost());
        $this->assertSame(500.0, $lastFleet->getShips()[1]->getCost());
        $this->assertSame(2123456.78, $lastFleet->getShips()[2]->getCost());
    }

    /**
     * @group functional
     * @group api
     */
    public function testBadCosts(): void
    {
        file_put_contents(sys_get_temp_dir().'/test-fleet.json', <<<EOT
                [
                  {
                    "manufacturer": "Drake",
                    "name": "Cutlass Black",
                    "cost": "$5,00",
                    "pledge_date": "April 28, 2018"
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

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('invalid_fleet_data', $json['error']);
        $this->assertSame('The fleet data in your file is invalid. Please check it.', $json['errorMessage']);
    }

    /**
     * @group functional
     * @group api
     */
    public function testInsuranceType(): void
    {
        file_put_contents(sys_get_temp_dir().'/test-fleet.json', <<<EOT
                [
                  {
                    "manufacturer": "Drake",
                    "name": "Cutlass Black",
                    "pledge_date": "April 28, 2018",
                    "insurance_type": "lti"
                  },
                  {
                    "manufacturer": "Drake",
                    "name": "Cutlass Black",
                    "pledge_date": "April 28, 2018",
                    "insurance_type": "iae"
                  },
                  {
                    "manufacturer": "Drake",
                    "name": "Cutlass Black",
                    "pledge_date": "April 28, 2018",
                    "insurance_type": "monthly",
                    "insurance_duration": 3
                  },
                  {
                    "manufacturer": "Drake",
                    "name": "Cutlass Black",
                    "pledge_date": "April 28, 2018",
                    "insurance_type": null
                  },
                  {
                    "manufacturer": "Drake",
                    "name": "Cutlass Black",
                    "pledge_date": "April 28, 2018",
                    "lti": false,
                    "monthsInsurance": 6
                  },
                  {
                    "manufacturer": "Drake",
                    "name": "Cutlass Black",
                    "pledge_date": "April 28, 2018",
                    "lti": true
                  },
                  {
                    "manufacturer": "Drake",
                    "name": "Cutlass Black",
                    "pledge_date": "April 28, 2018",
                    "lti": false,
                    "monthsInsurance": 6,
                    "insurance_duration": 5
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
        $this->assertCount(7, $lastFleet->getShips());

        $this->assertSame('lti', $lastFleet->getShips()[0]->getInsuranceType());
        $this->assertNull($lastFleet->getShips()[0]->getInsuranceDuration());
        $this->assertSame('iae', $lastFleet->getShips()[1]->getInsuranceType());
        $this->assertNull($lastFleet->getShips()[1]->getInsuranceDuration());
        $this->assertSame('monthly', $lastFleet->getShips()[2]->getInsuranceType());
        $this->assertSame(3, $lastFleet->getShips()[2]->getInsuranceDuration());
        $this->assertNull($lastFleet->getShips()[3]->getInsuranceType());
        $this->assertSame(6, $lastFleet->getShips()[4]->getInsuranceDuration());
        $this->assertFalse($lastFleet->getShips()[4]->isInsured());
        $this->assertTrue($lastFleet->getShips()[5]->isInsured());
        $this->assertSame(5, $lastFleet->getShips()[6]->getInsuranceDuration()); // prioritize to insurance_duration
    }
}
