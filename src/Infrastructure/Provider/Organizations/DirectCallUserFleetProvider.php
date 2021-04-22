<?php

namespace App\Infrastructure\Provider\Organizations;

use App\Application\MyFleet\MyFleetService;
use App\Application\MyFleet\Output\MyFleetShipOutput;
use App\Application\Provider\UserFleetProviderInterface;
use App\Domain\Exception\NotFoundFleetByUserException;
use App\Domain\MemberId;
use App\Domain\UserFleet;
use App\Domain\UserId;
use App\Domain\UserShip;

class DirectCallUserFleetProvider implements UserFleetProviderInterface
{
    public function __construct(
        private MyFleetService $myFleetService,
    ) {
    }

    public function getUserFleet(MemberId $memberId): UserFleet
    {
        $userId = UserId::fromString((string) $memberId);

        try {
            $fleet = $this->myFleetService->handle($userId);
        } catch (NotFoundFleetByUserException) {
            return new UserFleet($userId, []);
        }

        return new UserFleet(
            $userId,
            array_map(static function (MyFleetShipOutput $shipOutput): UserShip {
                return new UserShip(
                    $shipOutput->id,
                    $shipOutput->model,
                    $shipOutput->imageUrl,
                    $shipOutput->quantity,
                );
            }, $fleet->ships->items),
        );
    }
}
