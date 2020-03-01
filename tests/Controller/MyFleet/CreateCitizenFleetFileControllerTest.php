<?php

namespace App\Tests\Controller\MyFleet;

use App\Entity\User;
use App\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CreateCitizenFleetFileControllerTest extends WebTestCase
{
    /** @var User */
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Ioni']);
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
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_auth', $json['error']);
    }
}
