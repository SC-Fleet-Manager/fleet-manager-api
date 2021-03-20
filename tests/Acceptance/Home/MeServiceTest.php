<?php

namespace App\Tests\Acceptance\Home;

use App\Application\Exception\NotFoundUserException;
use App\Application\Home\MeService;
use App\Application\Home\Output\MeOutput;
use App\Application\Repository\UserRepositoryInterface;
use App\Domain\UserId;
use App\Entity\User;
use App\Infrastructure\Repository\InMemoryUserRepository;
use App\Tests\Acceptance\KernelTestCase;
use Symfony\Component\Uid\Ulid;

class MeServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_return_basic_logged_user_infos(): void
    {
        /** @var InMemoryUserRepository $userRepository */
        $userRepository = static::$container->get(UserRepositoryInterface::class);
        $userRepository->setUsers([
            new User(new UserId(Ulid::fromString('00000000000000000000000001')), 'Ioni', new \DateTimeImmutable('2021-03-20T17:42:00Z')),
        ]);

        /** @var MeService $service */
        $service = static::$container->get(MeService::class);
        $output = $service->handle(new UserId(Ulid::fromString('00000000000000000000000001')));

        static::assertEquals(new MeOutput(
            id: new UserId(Ulid::fromString('00000000000000000000000001')),
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
        $service->handle(new UserId(Ulid::fromString('00000000000000000000000001')));
    }
}
