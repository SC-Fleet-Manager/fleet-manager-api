<?php

namespace App\Domain\Exception;

use App\Domain\ShipId;
use App\Domain\UserId;
use Throwable;

class NotFoundShipException extends \RuntimeException
{
    public UserId $userId;
    public ShipId $shipId;

    public function __construct(UserId $userId, ShipId $shipId, $message = '', $code = 0, Throwable $previous = null)
    {
        $message = $message ?: sprintf('Unable to find ship %s of user %s.', $shipId, $userId);
        parent::__construct($message, $code, $previous);
        $this->userId = $userId;
        $this->shipId = $shipId;
    }
}
