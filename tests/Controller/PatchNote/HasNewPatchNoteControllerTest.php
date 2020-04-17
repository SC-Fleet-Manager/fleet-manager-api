<?php

namespace App\Tests\Controller\PatchNote;

use App\Entity\User;
use App\Tests\WebTestCase;

class HasNewPatchNoteControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group patch_note
     */
    public function testUserHasAlreadyReadAllPatchNotes(): void
    {
        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Ioni']);
        $this->logIn($user);

        $this->client->xmlHttpRequest('GET', '/api/has-new-patch-note', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($json['hasNewPatchNote'], 'hasNewPatchNote is not false.');
    }

    /**
     * @group functional
     * @group patch_note
     */
    public function testUserHasNotReadAllPatchNotes(): void
    {
        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Ashuvidz']);
        $this->logIn($user);

        $this->client->xmlHttpRequest('GET', '/api/has-new-patch-note', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($json['hasNewPatchNote'], 'hasNewPatchNote is not false.');
    }

    /**
     * @group functional
     * @group patch_note
     */
    public function testIndexNotAuth(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/has-new-patch-note', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_auth', $json['error']);
    }
}
