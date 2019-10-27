<?php

namespace App\Event;

use HWI\Bundle\OAuthBundle\Event\GetResponseUserEvent;
use HWI\Bundle\OAuthBundle\HWIOAuthEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OAuthConnectSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            HWIOAuthEvents::CONNECT_CONFIRMED => 'onConnectConfirmed',
        ];
    }

    public function onConnectConfirmed(GetResponseUserEvent $event): void
    {
        $event->setResponse(new RedirectResponse('/profile'));
    }
}
