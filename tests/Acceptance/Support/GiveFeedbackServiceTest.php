<?php

namespace App\Tests\Acceptance\Support;

use App\Application\Repository\UserRepositoryInterface;
use App\Application\Support\GiveFeedbackService;
use App\Domain\UserId;
use App\Entity\User;
use App\Infrastructure\Repository\User\InMemoryUserRepository;
use App\Tests\Acceptance\KernelTestCase;
use Symfony\Component\Notifier\EventListener\NotificationLoggerListener;

class GiveFeedbackServiceTest extends KernelTestCase
{
    /**
     * @test
     */
    public function it_should_give_feedback_from_logged_user(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');

        /** @var InMemoryUserRepository $userRepository */
        $userRepository = static::$container->get(UserRepositoryInterface::class);
        $user = new User($userId, 'Ioni', null, new \DateTimeImmutable('2021-01-01T10:00:00Z'));
        $user->setCoins(1500);
        $user->provideProfile(nickname: 'Ioni', email: 'ioni@example.com', discordId: '1234567890');
        $userRepository->setUsers([$user]);

        /** @var GiveFeedbackService $service */
        $service = static::$container->get(GiveFeedbackService::class);
        $service->handle($userId, $user->getProfile(), "Here my description\n\nMultiline.", 'given@example.com', '9876543210');

        /** @var NotificationLoggerListener $notificationLoggerListener */
        $notificationLoggerListener = static::$container->get('notifier.logger_notification_listener');
        static::assertCount(1, $messages = $notificationLoggerListener->getEvents()->getMessages());
        static::assertSame(<<<EOT
            Email: ioni@example.com
            GivenEmail: given@example.com
            DiscordId: 1234567890
            GivenDiscordId: 9876543210
            Nickname: Ioni
            RegisteredAt: 2021-01-01
            Coins: 1500
            Here my description

            Multiline.
            EOT, $messages[0]->getSubject());
    }

    /**
     * @test
     */
    public function it_should_give_feedback_with_minimum_infos(): void
    {
        $userId = UserId::fromString('00000000-0000-0000-0000-000000000001');

        /** @var InMemoryUserRepository $userRepository */
        $userRepository = static::$container->get(UserRepositoryInterface::class);
        $user = new User($userId, 'Ioni', null, new \DateTimeImmutable('2021-01-01T10:00:00Z'));
        $userRepository->setUsers([$user]);

        /** @var GiveFeedbackService $service */
        $service = static::$container->get(GiveFeedbackService::class);
        $service->handle($userId, $user->getProfile(), 'Here my description', null, null);

        /** @var NotificationLoggerListener $notificationLoggerListener */
        $notificationLoggerListener = static::$container->get('notifier.logger_notification_listener');
        static::assertCount(1, $messages = $notificationLoggerListener->getEvents()->getMessages());
        static::assertSame(
            "Email: \nGivenEmail: \nDiscordId: \nGivenDiscordId: \nNickname: \nRegisteredAt: 2021-01-01\nCoins: 0\nHere my description",
            $messages[0]->getSubject()
        );
    }
}
