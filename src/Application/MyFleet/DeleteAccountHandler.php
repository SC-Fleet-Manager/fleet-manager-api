<?php

namespace App\Application\MyFleet;

use App\Application\Repository\FleetRepositoryInterface;
use App\Domain\Event\DeletedUserEvent;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class DeleteAccountHandler implements MessageHandlerInterface
{
    public function __construct(
        private FleetRepositoryInterface $fleetRepository,
    ) {
    }

    public function __invoke(DeletedUserEvent $event): void
    {
        $userId = $event->getUserId();

        $fleet = $this->fleetRepository->getFleetByUser($userId);
        if ($fleet === null) {
            return;
        }

        $this->fleetRepository->delete($fleet);
    }
}
