<?php

namespace App\Security\Listener;

use App\Security\Exception\AlreadyLinkedDiscordException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class AlreadyLinkedDiscordListener
{
    public function onException(ExceptionEvent $event): void
    {
//        $e = $event->getThrowable(); // TODO SF 4.4+
        $e = $event->getException();
        if (!$e instanceof AlreadyLinkedDiscordException) {
            return;
        }
        $event->setResponse(new RedirectResponse('/profile?error=already_linked_discord'));
    }
}
