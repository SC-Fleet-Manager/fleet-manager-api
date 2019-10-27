<?php

namespace App\Security\Exception;

use App\Entity\User;

class AlreadyLinkedDiscordException extends \RuntimeException
{
    public $userToLink;
    public $userAlreadyLinked;

    public function __construct(User $userToLink, User $userAlreadyLinked)
    {
        parent::__construct('Your Discord account is already linked to another user.');
        $this->userToLink = $userToLink;
        $this->userAlreadyLinked = $userAlreadyLinked;
    }
}
