<?php

namespace App\Tests\End2End\Controller\PatchNote;

use App\Tests\End2End\WebTestCase;

class LastPatchNotesControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_return_5_last_patch_notes_and_update_last_read_date_of_logged_user(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, supporter_visible, coins, created_at, last_patch_note_read_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ioni', false, 5, '2021-01-01T10:00:00+00:00', '2021-03-01T10:00:00+00:00');
                INSERT INTO patch_note(id, title, body, created_at)
                VALUES ('00000000-0000-0000-0000-000000000010', 'Title 0', 'Body 0', '2021-03-20T10:00:00+01:00'),
                       ('00000000-0000-0000-0000-000000000011', 'Title 1', 'Body 1', '2021-03-21T10:00:00+01:00'),
                       ('00000000-0000-0000-0000-000000000012', 'Title 2', 'Body 2', '2021-03-22T10:00:00+01:00'),
                       ('00000000-0000-0000-0000-000000000013', 'Title 3', 'Body 3', '2021-03-23T10:00:00+01:00'),
                       ('00000000-0000-0000-0000-000000000014', 'Title 4', 'Body 4', '2021-03-24T10:00:00+01:00'),
                       ('00000000-0000-0000-0000-000000000015', 'Title 5', 'Body 5', '2021-03-25T10:00:00+01:00');
            SQL
        );

        static::$client->xmlHttpRequest('GET', '/api/last-patch-notes', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(200, static::$client->getResponse()->getStatusCode());
        $json = json_decode(static::$client->getResponse()->getContent(), true);
        static::assertSame([
            'patchNotes' => [
                [
                    'id' => '00000000-0000-0000-0000-000000000015',
                    'title' => 'Title 5',
                    'body' => 'Body 5',
                    'link' => null,
                    'createdAt' => '2021-03-25T09:00:00+00:00',
                ],
                [
                    'id' => '00000000-0000-0000-0000-000000000014',
                    'title' => 'Title 4',
                    'body' => 'Body 4',
                    'link' => null,
                    'createdAt' => '2021-03-24T09:00:00+00:00',
                ],
                [
                    'id' => '00000000-0000-0000-0000-000000000013',
                    'title' => 'Title 3',
                    'body' => 'Body 3',
                    'link' => null,
                    'createdAt' => '2021-03-23T09:00:00+00:00',
                ],
                [
                    'id' => '00000000-0000-0000-0000-000000000012',
                    'title' => 'Title 2',
                    'body' => 'Body 2',
                    'link' => null,
                    'createdAt' => '2021-03-22T09:00:00+00:00',
                ],
                [
                    'id' => '00000000-0000-0000-0000-000000000011',
                    'title' => 'Title 1',
                    'body' => 'Body 1',
                    'link' => null,
                    'createdAt' => '2021-03-21T09:00:00+00:00',
                ],
            ],
        ], $json);

        $result = static::$connection->executeQuery(<<<SQL
                SELECT last_patch_note_read_at FROM users WHERE id = '00000000-0000-0000-0000-000000000001';
            SQL
        )->fetchAssociative();
        static::assertSame('2021-03-25 09:00:00+00', $result['last_patch_note_read_at']);
    }
}
