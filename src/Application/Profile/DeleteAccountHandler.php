<?php

namespace App\Application\Profile;

use App\Application\Repository\Auth0RepositoryInterface;
use App\Domain\Event\DeletedUserEvent;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DeleteAccountHandler implements MessageHandlerInterface
{
    public function __construct(
        private Auth0RepositoryInterface $auth0Repository,
    ) {
    }

    public function __invoke(DeletedUserEvent $event): void
    {
        $username = $event->getAuth0Username();

        $this->auth0Repository->delete($username);
    }
}
