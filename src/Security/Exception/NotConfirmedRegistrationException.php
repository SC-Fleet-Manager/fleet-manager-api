<?php

namespace App\Security\Exception;

use Symfony\Component\Security\Core\Exception\AccountStatusException;

class NotConfirmedRegistrationException extends AccountStatusException
{
    public function getMessageKey(): string
    {
        return 'not_confirmed_registration';
    }
}
