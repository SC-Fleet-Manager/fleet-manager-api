<?php

namespace App\Tests\Acceptance\Profile;

use App\Application\Profile\DeleteAccountHandler;
use App\Application\Repository\Auth0RepositoryInterface;
use App\Application\Repository\UserRepositoryInterface;
use App\Domain\Event\DeletedUser;
use App\Domain\UserId;
use App\Entity\User;
use App\Infrastructure\Repository\User\FakeAuth0Repository;
use App\Infrastructure\Repository\User\InMemoryUserRepository;
use App\Tests\Acceptance\KernelTestCase;

class DeleteAccountHandlerTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_delete_the_auth0_data_of_deleted_user(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');

        /** @var InMemoryUserRepository $userRepository */
        $userRepository = static::$container->get(UserRepositoryInterface::class);
        $user = new User($userId, 'Ioni', new \DateTimeImmutable('2021-03-20T17:42:00+01:00'));
        $userRepository->setUsers([$user]);

        static::$container->get(DeleteAccountHandler::class)(new DeletedUser($userId, 'Ioni'));

        /** @var FakeAuth0Repository $auth0Repository */
        $auth0Repository = static::$container->get(Auth0RepositoryInterface::class);
        static::assertCount(1, $auth0Repository->getDeletedUsers());
        static::assertSame('Ioni', $auth0Repository->getDeletedUsers()[0]);
    }
}
