<?php

namespace App\Tests\Controller\Organization\Fleet;

use App\Entity\User;
use App\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportFleetControllerTest extends WebTestCase
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
     * @group organization_fleet
     */
    public function testExportOrgaFleet(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/export-orga-fleet/flk', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        /** @var BinaryFileResponse $response */
        $response = $this->client->getResponse();
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('application/csv', $response->headers->get('Content-Type'));
        $this->assertSame('attachment; filename=export_flk.csv', $response->headers->get('Content-Disposition'));
    }

    /**
     * @group functional
     * @group organization_fleet
     */
    public function testExportOrgaFleetNotAuth(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/export-orga-fleet/flk', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_auth', $json['error']);
    }
}
