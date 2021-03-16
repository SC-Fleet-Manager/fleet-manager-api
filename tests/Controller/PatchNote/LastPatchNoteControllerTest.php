<?php

namespace App\Tests\Controller\PatchNote;

use App\Entity\User;
use App\Tests\WebTestCase;

class LastPatchNoteControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group patch_note
     */
    public function testIndex(): void
    {
        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Gardien1']);
        static::assertNull($user->getLastPatchNoteReadAt(), 'The LastPatchNoteReadAt property is not null.');

        $this->client->xmlHttpRequest('GET', '/api/last-patch-notes', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Gardien1'),
        ]);

        static::assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        static::assertArraySubset([
            'patchNotes' => [
                [
                    'id' => '686afb82-c344-4b25-be84-5d327b2af47b',
                    'title' => 'My new patch 2',
                    'body' => "Hello everyone,\nHere is my new patch 2\nGoodbye!\n",
                    'link' => 'https://blog.fleet-manager.space/my-patch-2',
                    'createdAt' => '2019-04-04T11:22:33+00:00',
                ],
                [
                    'id' => '2d3e46c8-f783-45c6-a4de-92978985b8a6',
                    'title' => 'My new patch 1',
                    'body' => "Hello everyone,\nHere is my new patch 1\nGoodbye!\n",
                    'link' => 'https://blog.fleet-manager.space/my-patch-1',
                    'createdAt' => '2019-04-03T11:22:33+00:00',
                ],
            ],
        ], $json);

        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Ioni']);
        static::assertSame('2019-04-04T11:22:33+00:00', $user->getLastPatchNoteReadAt()->format('c'));
    }
}
