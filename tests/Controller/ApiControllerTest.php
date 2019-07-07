<?php

namespace App\Tests\Controller;

use App\Domain\CitizenNumber;
use App\Domain\HandleSC;
use App\Entity\Citizen;
use App\Entity\CitizenOrganization;
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

    /**
     * @group functional
     * @group api
     */
    public function testMe(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/me', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('d92e229e-e743-4583-905a-e02c57eacfe0', $json['id']);
        $this->assertSame('Ioni', $json['nickname']);
        $this->assertSame('2019-04-02T11:22:33+00:00', $json['createdAt']);
    }

    /**
     * @group functional
     * @group api
     */
    public function testMeNotAuth(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/me', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_auth', $json['error']);
    }

    /**
     * @group functional
     * @group api
     */
    public function testSearchHandle(): void
    {
        $citizen = new Citizen();
        $citizen->setActualHandle(new HandleSC('foobar'));
        $citizen->setNickname('Foo bar');
        $citizen->setNumber(new CitizenNumber('123456'));
        $citizen->setMainOrga((new CitizenOrganization())->setOrganizationSid('myorga')->setRank(1)->setRankName('Newbie'));

        $citizenInfosProvider = static::$container->get(CitizenInfosProviderInterface::class);
        $citizenInfosProvider->setCitizen($citizen);

        $this->client->xmlHttpRequest('GET', '/api/search-handle', [
            'handle' => 'foobar',
        ], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            'numberSC' => ['number' => '123456'],
            'nickname' => 'Foo bar',
            'handle' => ['handle' => 'foobar'],
            'mainOrga' => ['sid' => ['sid' => 'myorga'], 'rank' => 1, 'rankName' => 'Newbie'],
            'avatarUrl' => 'http://example.com/fake-avatar.png',
            'bio' => null,
        ], $json);
    }

    /**
     * @group functional
     * @group api
     */
    public function testSearchHandleNotFound(): void
    {
        $citizenInfosProvider = static::$container->get(CitizenInfosProviderInterface::class);
        $citizenInfosProvider->setCitizen(null);

        $this->client->xmlHttpRequest('GET', '/api/search-handle', [
            'handle' => 'not_exist',
        ], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_found_handle', $json['error']);
    }

    /**
     * @group functional
     * @group api
     */
    public function testSearchHandleNoParam(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/search-handle', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('invalid_form', $json['error']);
    }

    /**
     * @group functional
     * @group api
     */
    public function testOrganization(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/organization/flk', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            'id' => '80db0703-dd43-49a0-93d3-89947b9ab321',
            'organizationSid' => 'flk',
            'name' => 'FallKrom',
            'avatarUrl' => null,
            'publicChoice' => 'private',
        ], $json);
    }

    /**
     * @group functional
     * @group api
     */
    public function testOrganizationNotExist(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/organization/not_exist', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('orga_not_exist', $json['error']);
    }

    /**
     * @group functional
     * @group api
     */
    public function testManageableOrganizations(): void
    {
        $highRankUser = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Ashuvidz']);
        $this->logIn($highRankUser);
        $this->client->xmlHttpRequest('GET', '/api/manageable-organizations', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            [
                'id' => '80db0703-dd43-49a0-93d3-89947b9ab321',
                'organizationSid' => 'flk',
                'name' => 'FallKrom',
                'avatarUrl' => null,
                'publicChoice' => 'private',
            ],
        ], $json);
    }

    /**
     * @group functional
     * @group api
     */
    public function testManageableOrganizationsLowRank(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/manageable-organizations', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEmpty($json);
    }

    /**
     * @group functional
     * @group api
     */
    public function testManageableOrganizationsNoCitizen(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'NoCitizen']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/manageable-organizations', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_citizen_created', $json['error']);
    }

    /**
     * @group functional
     * @group api
     */
    public function testManageableOrganizationsNotAuth(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/manageable-organizations', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_auth', $json['error']);
    }

    /**
     * @group functional
     * @group api
     * @group webextension
     */
    public function testExportOptionsCors(): void
    {
        $this->client->request('OPTIONS', '/api/export');
        $this->assertSame(204, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group api
     * @group webextension
     */
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

        $this->assertTrue($lastFleet->getId()->equals($this->user->getCitizen()->getLastFleet()->getId()), 'Last fleet is inconsistent.');
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

    /**
     * @group functional
     * @group api
     */
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

    /**
     * @group functional
     * @group api
     */
    public function testCreateCitizenFleetFileNotAuth(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/create-citizen-fleet-file', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_auth', $json['error']);
    }

    /**
     * @group functional
     * @group api
     */
    public function testNumbers(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/numbers', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            'organizations' => 3,
            'users' => 10,
            'ships' => 10,
        ], $json);
    }
}
