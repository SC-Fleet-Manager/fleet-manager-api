<?php

namespace App\Tests\Acceptance\Profile;

use App\Domain\Exception\NotFoundUserException;
use App\Application\Profile\SavePreferencesService;
use App\Application\Repository\UserRepositoryInterface;
use App\Domain\UserId;
use App\Entity\User;
use App\Infrastructure\Repository\User\InMemoryUserRepository;
use App\Tests\Acceptance\KernelTestCase;

class SavePreferencesServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_turn_off_supporter_visible_setting(): void
    {
        /** @var InMemoryUserRepository $userRepository */
        $userRepository = static::$container->get(UserRepositoryInterface::class);
        $user = new User(UserId::fromString('00000000-0000-0000-0000-000000000001'), 'Ioni', null, new \DateTimeImmutable('2021-03-20T17:42:00+01:00'));
        $user->setSupporterVisible(true);
        $userRepository->setUsers([$user]);

        /** @var SavePreferencesService $service */
        $service = static::$container->get(SavePreferencesService::class);
        $service->handle(UserId::fromString('00000000-0000-0000-0000-000000000001'), false);

        static::assertFalse($user->isSupporterVisible(), 'supporterVisible should be false');
    }

    /**
     * @test
     */
    public function it_should_throw_exception_for_unknown_user(): void
    {
        $this->expectException(NotFoundUserException::class);

        /** @var SavePreferencesService $service */
        $service = static::$container->get(SavePreferencesService::class);
        $service->handle(UserId::fromString('00000000-0000-0000-0000-000000000001'), true);
    }
}
