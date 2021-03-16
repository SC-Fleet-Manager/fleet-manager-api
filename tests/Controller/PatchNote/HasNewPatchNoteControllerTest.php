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
        $this->client->xmlHttpRequest('GET', '/api/has-new-patch-note', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        static::assertFalse($json['hasNewPatchNote'], 'hasNewPatchNote is not false.');
    }

    /**
     * @group functional
     * @group patch_note
     */
    public function testUserHasNotReadAllPatchNotes(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/has-new-patch-note', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ashuvidz'),
        ]);

        static::assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        static::assertTrue($json['hasNewPatchNote'], 'hasNewPatchNote is not false.');
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

        static::assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        static::assertSame('forbidden', $json['error']);
    }
}
