<?php

namespace App\Tests\Controller;

use App\Entity\Fleet;
use App\Entity\User;
use App\Service\CitizenInfosProviderInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ApiControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    private $client;
    private $doctrine;
    /** @var User */
    private $user;

    protected static function createClient(array $options = array(), array $server = array())
    {
        static::bootKernel($options);

        $client = static::$kernel->getContainer()->get('test.client');
        $client->setServerParameters($server);

        return $client;
    }

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->doctrine = static::$container->get('doctrine');
        $this->user =  $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Ioni']);
    }

    private function logIn(User $user): void
    {
        $session = $this->client->getContainer()->get('session');

        $token = new OAuthToken('123456789123456789abcdefg', ['ROLE_USER']);
        $token->setResourceOwnerName('discord');
        $token->setUser($user);
        $token->setAuthenticated(true);
        $session->set('_security_main', serialize($token));
        $session->save();

        $this->client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));
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
        $this->client->xmlHttpRequest('GET', '/api/create-organisation-fleet-file/flk', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        /** @var BinaryFileResponse $response */
        $response = $this->client->getResponse();
        $this->assertSame('application/json', $response->headers->get('Content-Type'));
        $this->assertSame('attachment; filename=organisation_fleet.json', $response->headers->get('Content-Disposition'));
    }
}
