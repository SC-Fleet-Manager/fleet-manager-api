<?php

namespace App\Tests\Integration\Repository\PatchNoteRepository;

use App\Infrastructure\Repository\PatchNote\DoctrinePatchNoteRepository;
use App\Tests\Integration\KernelTestCase;

class GetOneRecentPatchNoteIdTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_return_a_recent_patch_note_id(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO patch_note(id, title, body, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', 'Title', 'Body', '2021-01-01T10:00:00+01:00'),
                       ('00000000-0000-0000-0000-000000000002', 'Title', 'Body', '2021-01-15T10:00:00+01:00');
            SQL
        );

        /** @var DoctrinePatchNoteRepository $patchNoteRepository */
        $patchNoteRepository = static::$container->get(DoctrinePatchNoteRepository::class);
        $patchNoteId = $patchNoteRepository->getOneRecentPatchNoteId(new \DateTimeImmutable('2021-01-10T10:00:00+01:00'));

        static::assertSame('00000000-0000-0000-0000-000000000002', (string) $patchNoteId);
    }

    /**
     * @test
     */
    public function it_should_return_any_patch_note_id_with_no_date(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO patch_note(id, title, body, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', 'Title', 'Body', '2021-01-01T10:00:00+01:00');
            SQL
        );

        /** @var DoctrinePatchNoteRepository $patchNoteRepository */
        $patchNoteRepository = static::$container->get(DoctrinePatchNoteRepository::class);
        $patchNoteId = $patchNoteRepository->getOneRecentPatchNoteId(null);

        static::assertSame('00000000-0000-0000-0000-000000000001', (string) $patchNoteId);
    }

    /**
     * @test
     */
    public function it_should_not_return_patch_note_id_with_a_enough_recent_date(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO patch_note(id, title, body, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', 'Title', 'Body', '2020-01-01T10:00:00+01:00'),
                       ('00000000-0000-0000-0000-000000000002', 'Title', 'Body', '2020-10-05T10:00:00+02:00'),
                       ('00000000-0000-0000-0000-000000000003', 'Title', 'Body', '2021-02-15T10:00:00+00:00');
            SQL
        );

        /** @var DoctrinePatchNoteRepository $patchNoteRepository */
        $patchNoteRepository = static::$container->get(DoctrinePatchNoteRepository::class);
        $patchNoteId = $patchNoteRepository->getOneRecentPatchNoteId(new \DateTimeImmutable('2021-03-01T10:00:00+01:00'));

        static::assertNull($patchNoteId);
    }
}
