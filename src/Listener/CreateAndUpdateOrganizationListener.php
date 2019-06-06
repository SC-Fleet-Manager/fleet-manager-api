<?php

namespace App\Listener;

use App\Event\CitizenRefreshEvent;
use App\Service\OrganizationCreator;

class CreateAndUpdateOrganizationListener
{
    private $organizationCreator;

    public function __construct(OrganizationCreator $organizationCreator)
    {
        $this->organizationCreator = $organizationCreator;
    }

    public function onRefreshCitizen(CitizenRefreshEvent $event): void
    {
        $this->organizationCreator->createAndUpdateOrganizations(iterator_to_array($event->getCitizen()->getOrganizations()));
    }
}
