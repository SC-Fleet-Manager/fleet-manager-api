<?php

namespace App\Tests\Controller\PatchNote;

use App\Tests\WebTestCase;

class HasNewPatchNoteControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group patch_note
     */
    public function test_user_has_already_read_all_patch_notes(): void
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
    public function test_user_has_not_read_all_patch_notes(): void
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
    public function test_index_not_auth(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/has-new-patch-note', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        static::assertSame('forbidden', $json['error']);
    }
}
