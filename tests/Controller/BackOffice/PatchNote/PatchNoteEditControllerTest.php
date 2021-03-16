<?php

namespace App\Tests\Controller\BackOffice\PatchNote;

use App\Tests\WebTestCase;

class PatchNoteEditControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group patch_note
     * @group bo
     */
    public function testNotAuth(): void
    {
        $this->client->request('GET', '/bo/patch-note/edit/2d3e46c8-f783-45c6-a4de-92978985b8a6');

        static::assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group patch_note
     * @group bo
     */
    public function testNotAdmin(): void
    {
        $this->client->request('GET', '/bo/patch-note/edit/2d3e46c8-f783-45c6-a4de-92978985b8a6', [], [], [
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
        $this->client->request('GET', '/bo/patch-note/edit/2d3e46c8-f783-45c6-a4de-92978985b8a6', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group patch_note
     * @group bo
     */
    public function testNotExist(): void
    {
        $this->client->request('GET', '/bo/patch-note/edit/a4491559-e9bd-465d-85e2-a810dcedc275', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]); // fake uuid

        static::assertSame(404, $this->client->getResponse()->getStatusCode());
    }
}
