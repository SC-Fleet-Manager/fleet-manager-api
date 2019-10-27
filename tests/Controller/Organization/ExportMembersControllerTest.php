<?php

namespace App\Tests\Controller\Organization;

use App\Entity\User;
use App\Tests\WebTestCase;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportMembersControllerTest extends WebTestCase
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
     * @group organization
     */
    public function testExportMembersSuccess(): void
    {
        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Ashuvidz']); // admin
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/organization/export-orga-members/flk', [], [], [
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
     * @group organization
     */
    public function testExportMembersForbidden(): void
    {
        $this->logIn($this->user); // not admin
        $this->client->xmlHttpRequest('GET', '/api/organization/export-orga-members/flk', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights', $json['error']);
    }
}
