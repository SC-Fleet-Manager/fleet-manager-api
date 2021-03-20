<?php

namespace App\Tests\Integration\Repository\UserRepository;

use App\Application\Repository\UserRepositoryInterface;
use App\Infrastructure\Repository\User\DoctrineUserRepository;
use App\Tests\Integration\KernelTestCase;

class CountUsersTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_return_2_users(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, supporter_visible, coins, created_at, last_patch_note_read_at)
                VALUES ('00000000-0000-0000-0000-000000000000', '["ROLE_USER"]', 'Ioni', true, 0, '2021-03-20T15:50:00Z', '2021-03-20T15:50:00Z'),
                       ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'Ashuvidz', true, 0, '2021-03-20T15:50:00Z', '2021-03-20T15:50:00Z');
            SQL
        );

        /** @var UserRepositoryInterface $userRepository */
        $userRepository = static::$container->get(DoctrineUserRepository::class);
        $count = $userRepository->countUsers();

        static::assertSame(2, $count);
    }

    /**
     * @test
     */
    public function it_should_return_0_if_no_users(): void
    {
        /** @var UserRepositoryInterface $userRepository */
        $userRepository = static::$container->get(DoctrineUserRepository::class);
        $count = $userRepository->countUsers();

        static::assertSame(0, $count);
    }
}
