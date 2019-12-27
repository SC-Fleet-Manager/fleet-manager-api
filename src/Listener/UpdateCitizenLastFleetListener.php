<?php

namespace App\Listener;

use App\Entity\Citizen;
use App\Entity\Fleet;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class UpdateCitizenLastFleetListener
{
    public function prePersist(LifecycleEventArgs $event): void
    {
        $entity = $event->getObject();
        if (!$entity instanceof Fleet) {
            return;
        }
        $this->updateLastFleet($entity->getOwner());
    }

    private function updateLastFleet(Citizen $citizen): void
    {
        $lastFleet = null;
        foreach ($citizen->getFleets() as $fleet) {
            if ($lastFleet === null || $fleet->getVersion() > $lastFleet->getVersion()) {
                $lastFleet = $fleet;
            }
        }
        $citizen->setLastFleet($lastFleet);
    }
}
