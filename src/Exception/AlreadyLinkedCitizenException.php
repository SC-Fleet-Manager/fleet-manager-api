<?php

namespace App\Exception;

use App\Entity\Citizen;
use App\Entity\User;

class AlreadyLinkedCitizenException extends \RuntimeException
{
    public $user;
    public $citizen;

    public function __construct(User $user, Citizen $citizen, $message = '', $code = 0, \Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->user = $user;
        $this->citizen = $citizen;
    }
}
