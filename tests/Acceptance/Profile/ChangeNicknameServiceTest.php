<?php

namespace App\Tests\Acceptance\Profile;

use App\Application\Profile\ChangeNicknameService;
use App\Application\Repository\UserRepositoryInterface;
use App\Domain\Exception\NotFoundUserException;
use App\Domain\UserId;
use App\Entity\User;
use App\Infrastructure\Repository\User\InMemoryUserRepository;
use App\Tests\Acceptance\KernelTestCase;

class ChangeNicknameServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_change_the_nickname_of_logged_user(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');

        /** @var InMemoryUserRepository $userRepository */
        $userRepository = static::$container->get(UserRepositoryInterface::class);
        $user = new User($userId, 'Ioni', null, new \DateTimeImmutable('2021-03-20T17:42:00+01:00'));
        $user->setSupporterVisible(true);
        $userRepository->setUsers([$user]);

        /** @var ChangeNicknameService $service */
        $service = static::$container->get(ChangeNicknameService::class);
        $service->handle($userId, 'my_nickname');

        $user = $userRepository->getById($userId);
        static::assertSame('my_nickname', $user->getNickname());
    }

    /**
     * @test
     */
    public function it_should_reset_the_nickname_of_logged_user_by_filling_null(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');

        /** @var InMemoryUserRepository $userRepository */
        $userRepository = static::$container->get(UserRepositoryInterface::class);
        $user = new User($userId, 'Ioni', 'my_nickname', new \DateTimeImmutable('2021-03-20T17:42:00+01:00'));
        $user->setSupporterVisible(true);
        $userRepository->setUsers([$user]);

        /** @var ChangeNicknameService $service */
        $service = static::$container->get(ChangeNicknameService::class);
        $service->handle($userId, null);

        $user = $userRepository->getById($userId);
        static::assertNull($user->getNickname());
    }

    /**
     * @test
     */
    public function it_should_throw_exception_for_unknown_user(): void
    {
        $this->expectException(NotFoundUserException::class);

        /** @var ChangeNicknameService $service */
        $service = static::$container->get(ChangeNicknameService::class);
        $service->handle(UserId::fromString('00000000-0000-0000-0000-000000000001'), true);
    }
}
