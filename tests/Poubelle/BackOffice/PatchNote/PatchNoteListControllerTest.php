<?php

namespace App\Tests\Controller\BackOffice\PatchNote;

use App\Tests\WebTestCase;

class PatchNoteListControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group patch_note
     * @group bo
     */
    public function test_not_auth(): void
    {
        $this->client->request('GET', '/bo/patch-note/list');

        static::assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group patch_note
     * @group bo
     */
    public function test_not_admin(): void
    {
        $this->client->request('GET', '/bo/patch-note/list', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Gardien1'),
        ]);

        static::assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group patch_note
     * @group bo
     */
    public function test_admin(): void
    {
        $crawler = $this->client->request('GET', '/bo/patch-note/list', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(200, $this->client->getResponse()->getStatusCode());
        static::assertSame('Patch Note List', $crawler->filter('h1')->text());
    }
}
