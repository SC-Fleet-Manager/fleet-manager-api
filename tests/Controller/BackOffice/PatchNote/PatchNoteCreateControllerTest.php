<?php

namespace App\Tests\Controller\BackOffice\PatchNote;

use App\Tests\WebTestCase;

class PatchNoteCreateControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group patch_note
     * @group bo
     */
    public function testNotAuth(): void
    {
        $this->client->request('GET', '/bo/patch-note/create');

        static::assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group patch_note
     * @group bo
     */
    public function testNotAdmin(): void
    {
        $this->client->request('GET', '/bo/patch-note/create', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Gardien1'),
        ]);

        static::assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group patch_note
     * @group bo
     */
    public function testAdmin(): void
    {
        $this->client->request('GET', '/bo/patch-note/create', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(200, $this->client->getResponse()->getStatusCode());
    }
}
