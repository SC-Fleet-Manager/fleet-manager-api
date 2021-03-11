<?php

namespace App\Tests\Controller\BackOffice\PatchNote;

use App\Entity\User;
use App\Tests\WebTestCase;

class PatchNoteEditControllerTest extends WebTestCase
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
     * @group patch_note
     * @group bo
     */
    public function testNotAuth(): void
    {
        $this->client->request('GET', '/bo/patch-note/edit/2d3e46c8-f783-45c6-a4de-92978985b8a6');

        static::assertSame(401, $this->client->getResponse()->getStatusCode());
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
        $this->client->request('GET', '/bo/patch-note/edit/2d3e46c8-f783-45c6-a4de-92978985b8a6');

        static::assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group patch_note
     * @group bo
     */
    public function testAdmin(): void
    {
        $this->logIn($this->user); // ROLE_ADMIN
        $this->client->request('GET', '/bo/patch-note/edit/2d3e46c8-f783-45c6-a4de-92978985b8a6');

        static::assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group patch_note
     * @group bo
     */
    public function testNotExist(): void
    {
        $this->logIn($this->user); // ROLE_ADMIN
        $this->client->request('GET', '/bo/patch-note/edit/a4491559-e9bd-465d-85e2-a810dcedc275'); // fake uuid

        static::assertSame(404, $this->client->getResponse()->getStatusCode());
    }
}
