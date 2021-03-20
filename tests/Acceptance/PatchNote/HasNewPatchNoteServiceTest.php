<?php

namespace App\Tests\Acceptance\PatchNote;

use App\Application\Exception\NotFoundUserException;
use App\Application\PatchNote\HasNewPatchNoteService;
use App\Application\PatchNote\Output\HasNewPatchNoteOutput;
use App\Application\Profile\ProfileService;
use App\Application\Repository\PatchNoteRepositoryInterface;
use App\Application\Repository\UserRepositoryInterface;
use App\Domain\PatchNoteId;
use App\Domain\UserId;
use App\Entity\PatchNote;
use App\Entity\User;
use App\Infrastructure\Repository\PatchNote\InMemoryPatchNoteRepository;
use App\Infrastructure\Repository\User\InMemoryUserRepository;
use App\Tests\Acceptance\KernelTestCase;
use Symfony\Component\Uid\Ulid;

class HasNewPatchNoteServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_return_true_if_the_user_has_never_read_a_patch_note(): void
    {
        /** @var InMemoryUserRepository $userRepository */
        $userRepository = static::$container->get(UserRepositoryInterface::class);
        $user = new User(new UserId(Ulid::fromString('00000000000000000000000001')), 'Ioni', new \DateTimeImmutable('2021-03-20T17:42:00+01:00'));
        $user->setLastPatchNoteReadAt(null);
        $userRepository->setUsers([$user]);

        /** @var InMemoryPatchNoteRepository $patchNoteRepository */
        $patchNoteRepository = static::$container->get(PatchNoteRepositoryInterface::class);
        $patchNoteRepository->setPatchNotes([new PatchNote(new PatchNoteId(Ulid::fromString('00000000000000000000000010')), 'Title', 'Body', null, new \DateTimeImmutable('2021-03-19T17:42:00+01:00'))]);

        /** @var HasNewPatchNoteService $service */
        $service = static::$container->get(HasNewPatchNoteService::class);
        $output = $service->handle(new UserId(Ulid::fromString('00000000000000000000000001')));

        static::assertEquals(new HasNewPatchNoteOutput(
            hasNewPatchNote: true,
        ), $output);
    }

    /**
     * @test
     */
    public function it_should_return_true_if_the_user_has_a_new_patch_note_to_read(): void
    {
        /** @var InMemoryUserRepository $userRepository */
        $userRepository = static::$container->get(UserRepositoryInterface::class);
        $user = new User(new UserId(Ulid::fromString('00000000000000000000000001')), 'Ioni', new \DateTimeImmutable('2021-03-17T17:42:00+01:00'));
        $user->setLastPatchNoteReadAt(new \DateTimeImmutable('2021-03-19T16:41:00+00:00'));
        $userRepository->setUsers([$user]);

        /** @var InMemoryPatchNoteRepository $patchNoteRepository */
        $patchNoteRepository = static::$container->get(PatchNoteRepositoryInterface::class);
        $patchNoteRepository->setPatchNotes([new PatchNote(new PatchNoteId(Ulid::fromString('00000000000000000000000010')), 'Title', 'Body', null, new \DateTimeImmutable('2021-03-19T17:42:00+01:00'))]);

        /** @var HasNewPatchNoteService $service */
        $service = static::$container->get(HasNewPatchNoteService::class);
        $output = $service->handle(new UserId(Ulid::fromString('00000000000000000000000001')));

        static::assertEquals(new HasNewPatchNoteOutput(
            hasNewPatchNote: true,
        ), $output);
    }

    /**
     * @test
     */
    public function it_should_return_false_if_the_user_has_read_all_patch_notes(): void
    {
        /** @var InMemoryUserRepository $userRepository */
        $userRepository = static::$container->get(UserRepositoryInterface::class);
        $user = new User(new UserId(Ulid::fromString('00000000000000000000000001')), 'Ioni', new \DateTimeImmutable('2021-03-17T17:42:00+01:00'));
        $user->setLastPatchNoteReadAt(new \DateTimeImmutable('2021-03-20T16:41:00+00:00'));
        $userRepository->setUsers([$user]);

        /** @var InMemoryPatchNoteRepository $patchNoteRepository */
        $patchNoteRepository = static::$container->get(PatchNoteRepositoryInterface::class);
        $patchNoteRepository->setPatchNotes([new PatchNote(new PatchNoteId(Ulid::fromString('00000000000000000000000010')), 'Title', 'Body', null, new \DateTimeImmutable('2021-03-19T17:42:00+01:00'))]);

        /** @var HasNewPatchNoteService $service */
        $service = static::$container->get(HasNewPatchNoteService::class);
        $output = $service->handle(new UserId(Ulid::fromString('00000000000000000000000001')));

        static::assertEquals(new HasNewPatchNoteOutput(
            hasNewPatchNote: false,
        ), $output);
    }

    /**
     * @test
     */
    public function it_should_throw_exception_for_unknown_user(): void
    {
        $this->expectException(NotFoundUserException::class);

        /** @var ProfileService $service */
        $service = static::$container->get(ProfileService::class);
        $service->handle(new UserId(Ulid::fromString('00000000000000000000000001')));
    }
}
