<?php

namespace App\Application\Home;

use App\Application\Home\Output\NumbersOutput;
use App\Application\Repository\FleetRepositoryInterface;
use App\Application\Repository\OrganizationFleetRepositoryInterface;
use App\Application\Repository\UserRepositoryInterface;

class NumbersService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private OrganizationFleetRepositoryInterface $organizationFleetRepository,
        private FleetRepositoryInterface $fleetRepository,
    ) {
    }

    public function handle(): NumbersOutput
    {
        return new NumbersOutput(
            $this->userRepository->countUsers(),
            $this->organizationFleetRepository->countFleets(),
            $this->fleetRepository->countShips(),
        );
    }
}
