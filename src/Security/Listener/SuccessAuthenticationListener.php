<?php

namespace App\Security\Listener;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;

class SuccessAuthenticationListener
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function onAuthenticationSuccess(AuthenticationEvent $event): void
    {
        $token = $event->getAuthenticationToken();
        $user = $token->getUser();
        if (!$user instanceof User) {
            return;
        }

        $user->setLastConnectedAt(new \DateTimeImmutable());
        $this->entityManager->flush();
    }
}
