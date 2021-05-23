<?php

namespace App\Tests\End2End\Controller\Support;

use App\Tests\End2End\WebTestCase;
use Symfony\Component\Notifier\EventListener\NotificationLoggerListener;

class GiveFeedbackControllerTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_should_give_feedback_from_logged_user(): void
    {
        static::$connection->executeStatement(<<<SQL
                INSERT INTO users(id, roles, auth0_username, coins, created_at)
                VALUES ('00000000-0000-0000-0000-000000000001', '["ROLE_USER"]', 'oauth2|discord|1234567890', 1500, '2021-01-01T10:00:00Z');
            SQL
        );

        static::xhr('POST', '/api/support/give-feedback', [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('oauth2|discord|1234567890', nickname: 'Ioni_nickname', email: 'ioni@example.com'),
        ], json_encode([
            'description' => <<<EOT
                Here my description

                Multiline.
                EOT,
            'email' => 'given@example.com',
            'discordId' => '9876543210',
        ]));

        static::assertSame(204, static::$client->getResponse()->getStatusCode());

        /** @var NotificationLoggerListener $notificationLoggerListener */
        $notificationLoggerListener = static::$container->get('notifier.logger_notification_listener');
        static::assertCount(1, $messages = $notificationLoggerListener->getEvents()->getMessages());
        static::assertSame(<<<EOT
            Email: ioni@example.com
            GivenEmail: given@example.com
            DiscordId: 1234567890
            GivenDiscordId: 9876543210
            Nickname: Ioni_nickname
            RegisteredAt: 2021-01-01
            Coins: 1500
            Here my description

            Multiline.
            EOT, $messages[0]->getSubject());
    }

    /**
     * @test
     */
    public function it_should_return_error_if_not_logged(): void
    {
        static::xhr('POST', '/api/support/give-feedback', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(401, static::$client->getResponse()->getStatusCode());
        $json = static::json();
        static::assertSame(['message' => 'Authentication required.'], $json);
    }
}
