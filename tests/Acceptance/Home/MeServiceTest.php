<?php

namespace App\Tests\Acceptance\Home;

use App\Domain\Exception\NotFoundUserException;
use App\Application\Home\MeService;
use App\Application\Home\Output\MeOutput;
use App\Application\Repository\UserRepositoryInterface;
use App\Domain\UserId;
use App\Entity\User;
use App\Infrastructure\Repository\User\InMemoryUserRepository;
use App\Tests\Acceptance\KernelTestCase;

class MeServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_return_basic_logged_user_infos(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');

        /** @var InMemoryUserRepository $userRepository */
        $userRepository = static::$container->get(UserRepositoryInterface::class);
        $userRepository->setUsers([
            new User($userId, 'Ioni', null, new \DateTimeImmutable('2021-03-20T17:42:00Z')),
        ]);

        /** @var MeService $service */
        $service = static::$container->get(MeService::class);
        $output = $service->handle($userId);

        static::assertEquals(new MeOutput(
            id: $userId,
            createdAt: new \DateTimeImmutable('2021-03-20T17:42:00Z'),
        ), $output);
    }

    /**
     * @test
     */
    public function it_should_throw_exception_for_unknown_user(): void
    {
        $this->expectException(NotFoundUserException::class);

        /** @var MeService $service */
        $service = static::$container->get(MeService::class);
        $service->handle(UserId::fromString('00000000-0000-0000-0000-000000000001'));
    }
}
