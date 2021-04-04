<?php

namespace App\Tests\Acceptance\PatchNote;

use App\Application\PatchNote\LastPatchNotesService;
use App\Application\PatchNote\Output\LastPatchNoteOutput;
use App\Application\PatchNote\Output\LastPatchNotesOutput;
use App\Application\Repository\PatchNoteRepositoryInterface;
use App\Application\Repository\UserRepositoryInterface;
use App\Domain\PatchNoteId;
use App\Domain\UserId;
use App\Entity\PatchNote;
use App\Entity\User;
use App\Infrastructure\Repository\PatchNote\InMemoryPatchNoteRepository;
use App\Infrastructure\Repository\User\InMemoryUserRepository;
use App\Tests\Acceptance\KernelTestCase;

class LastPatchNotesServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_return_the_last_5_patch_notes_over_6(): void
    {
        /** @var InMemoryPatchNoteRepository $patchNoteRepository */
        $patchNoteRepository = static::$container->get(PatchNoteRepositoryInterface::class);
        $patchNoteRepository->setPatchNotes([
            new PatchNote(PatchNoteId::fromString('00000000000000000000000010'), 'Title 0', 'Body 0', null, new \DateTimeImmutable('2021-01-01T10:00:00+00:00')),
            new PatchNote(PatchNoteId::fromString('00000000000000000000000011'), 'Title 1', 'Body 1', null, new \DateTimeImmutable('2021-01-02T10:00:00+00:00')),
            new PatchNote(PatchNoteId::fromString('00000000000000000000000012'), 'Title 2', 'Body 2', 'http://example.com/2', new \DateTimeImmutable('2021-01-03T10:00:00+00:00')),
            new PatchNote(PatchNoteId::fromString('00000000000000000000000013'), 'Title 3', 'Body 3', 'http://example.com/3', new \DateTimeImmutable('2021-01-04T10:00:00+00:00')),
            new PatchNote(PatchNoteId::fromString('00000000000000000000000014'), 'Title 4', 'Body 4', null, new \DateTimeImmutable('2021-01-05T10:00:00+00:00')),
            new PatchNote(PatchNoteId::fromString('00000000000000000000000015'), 'Title 5', 'Body 5', null, new \DateTimeImmutable('2021-01-06T10:00:00+00:00')),
        ]);

        /** @var LastPatchNotesService $service */
        $service = static::$container->get(LastPatchNotesService::class);
        $output = $service->handle();

        static::assertEquals(new LastPatchNotesOutput(patchNotes: [
            new LastPatchNoteOutput(id: PatchNoteId::fromString('00000000000000000000000015'), title: 'Title 5', body: 'Body 5', link: null, createdAt: new \DateTimeImmutable('2021-01-06T10:00:00+00:00')),
            new LastPatchNoteOutput(id: PatchNoteId::fromString('00000000000000000000000014'), title: 'Title 4', body: 'Body 4', link: null, createdAt: new \DateTimeImmutable('2021-01-05T10:00:00+00:00')),
            new LastPatchNoteOutput(id: PatchNoteId::fromString('00000000000000000000000013'), title: 'Title 3', body: 'Body 3', link: 'http://example.com/3', createdAt: new \DateTimeImmutable('2021-01-04T10:00:00+00:00')),
            new LastPatchNoteOutput(id: PatchNoteId::fromString('00000000000000000000000012'), title: 'Title 2', body: 'Body 2', link: 'http://example.com/2', createdAt: new \DateTimeImmutable('2021-01-03T10:00:00+00:00')),
            new LastPatchNoteOutput(id: PatchNoteId::fromString('00000000000000000000000011'), title: 'Title 1', body: 'Body 1', link: null, createdAt: new \DateTimeImmutable('2021-01-02T10:00:00+00:00')),
        ]), $output);
    }

    /**
     * @test
     */
    public function it_should_return_the_only_2_patch_notes_ordered_by_created_date(): void
    {
        /** @var InMemoryPatchNoteRepository $patchNoteRepository */
        $patchNoteRepository = static::$container->get(PatchNoteRepositoryInterface::class);
        $patchNoteRepository->setPatchNotes([
            new PatchNote(PatchNoteId::fromString('00000000000000000000000010'), 'Title 0', 'Body 0', null, new \DateTimeImmutable('2021-01-05T10:00:00+00:00')),
            new PatchNote(PatchNoteId::fromString('00000000000000000000000011'), 'Title 1', 'Body 1', null, new \DateTimeImmutable('2021-01-01T10:00:00+00:00')),
        ]);

        /** @var LastPatchNotesService $service */
        $service = static::$container->get(LastPatchNotesService::class);
        $output = $service->handle();

        static::assertEquals(new LastPatchNotesOutput(patchNotes: [
            new LastPatchNoteOutput(id: PatchNoteId::fromString('00000000000000000000000010'), title: 'Title 0', body: 'Body 0', link: null, createdAt: new \DateTimeImmutable('2021-01-05T10:00:00+00:00')),
            new LastPatchNoteOutput(id: PatchNoteId::fromString('00000000000000000000000011'), title: 'Title 1', body: 'Body 1', link: null, createdAt: new \DateTimeImmutable('2021-01-01T10:00:00+00:00')),
        ]), $output);
    }

    /**
     * @test
     */
    public function it_should_update_last_read_date_of_logged_user(): void
    {
        /** @var InMemoryPatchNoteRepository $patchNoteRepository */
        $patchNoteRepository = static::$container->get(PatchNoteRepositoryInterface::class);
        $patchNoteRepository->setPatchNotes([
            new PatchNote(PatchNoteId::fromString('00000000000000000000000010'), 'Title 0', 'Body 0', null, new \DateTimeImmutable('2021-01-05T10:00:00+00:00')),
        ]);

        /** @var InMemoryUserRepository $userRepository */
        $userRepository = static::$container->get(UserRepositoryInterface::class);
        $user = new User(UserId::fromString('00000000000000000000000001'), 'Ioni', new \DateTimeImmutable('2021-03-17T17:42:00+01:00'));
        $user->setLastPatchNoteReadAt(new \DateTimeImmutable('2021-01-01T10:00:00+00:00'));
        $userRepository->setUsers([$user]);

        /** @var LastPatchNotesService $service */
        $service = static::$container->get(LastPatchNotesService::class);
        $service->handle(UserId::fromString('00000000000000000000000001'));

        static::assertEquals(new \DateTimeImmutable('2021-01-05T10:00:00+00:00'), $user->getLastPatchNoteReadAt());
    }
}
