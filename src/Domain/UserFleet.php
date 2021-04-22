<?php

namespace App\Domain;

class UserFleet
{
    private UserId $userId;
    /** @var UserShip[] */
    private array $ships;

    public function __construct(UserId $userId, array $ships)
    {
        $this->userId = $userId;
        $this->ships = $ships;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    /**
     * @return UserShip[]
     */
    public function getShips(): array
    {
        return $this->ships;
    }
}
