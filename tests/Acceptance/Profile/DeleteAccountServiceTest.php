<?php

namespace App\Tests\Acceptance\Profile;

use App\Application\Profile\DeleteAccountService;
use App\Application\Repository\UserRepositoryInterface;
use App\Domain\Event\DeletedUserEvent;
use App\Domain\Event\UpdatedFleetShipEvent;
use App\Domain\UserId;
use App\Entity\User;
use App\Infrastructure\Repository\User\InMemoryUserRepository;
use App\Tests\Acceptance\KernelTestCase;
use Symfony\Component\Messenger\Transport\InMemoryTransport;

class DeleteAccountServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_delete_the_logged_user_account_and_all_its_data(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');

        /** @var InMemoryUserRepository $userRepository */
        $userRepository = static::$container->get(UserRepositoryInterface::class);
        $user = new User($userId, 'Ioni', null, new \DateTimeImmutable('2021-03-20T17:42:00+01:00'));
        $userRepository->setUsers([$user]);

        /** @var DeleteAccountService $service */
        $service = static::$container->get(DeleteAccountService::class);
        $service->handle($userId);

        $user = $userRepository->getById($userId);
        static::assertNull($user, 'The user should be deleted.');

        /** @var InMemoryTransport $transport */
        $transport = static::$container->get('messenger.transport.my_fleet_internal');
        static::assertCount(1, $transport->getSent());
        /** @var DeletedUserEvent $message */
        $message = $transport->getSent()[0]->getMessage();
        static::assertInstanceOf(DeletedUserEvent::class, $message);
        static::assertSame('00000000-0000-0000-0000-000000000001', (string) $message->getUserId());

        /** @var InMemoryTransport $transport */
        $transport = static::$container->get('messenger.transport.organizations_sub');
        static::assertCount(1, $transport->getSent());
        /** @var DeletedUserEvent $message */
        $message = $transport->getSent()[0]->getMessage();
        static::assertInstanceOf(DeletedUserEvent::class, $message);
        static::assertSame('00000000-0000-0000-0000-000000000001', (string) $message->getUserId());
    }
}
