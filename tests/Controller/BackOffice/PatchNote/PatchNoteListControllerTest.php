<?php

namespace App\Tests\Controller\BackOffice\PatchNote;

use App\Entity\User;
use App\Tests\WebTestCase;

class PatchNoteListControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group patch_note
     * @group bo
     */
    public function testNotAuth(): void
    {
        $this->client->request('GET', '/bo/patch-note/list');

        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group patch_note
     * @group bo
     */
    public function testNotAdmin(): void
    {
        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Gardien1']); // ROLE_USER
        $this->logIn($user);
        $this->client->request('GET', '/bo/patch-note/list');

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group patch_note
     * @group bo
     */
    public function testAdmin(): void
    {
        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Ioni']); // ROLE_ADMIN
        $this->logIn($user);
        $crawler = $this->client->request('GET', '/bo/patch-note/list');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame('Patch Note List', $crawler->filter('h1')->text());
    }
}
