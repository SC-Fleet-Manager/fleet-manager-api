<?php

namespace App\Infrastructure\Controller\Profile\Input;

use Symfony\Component\Validator\Constraints\NotNull;

class SavePreferencesInput
{
    #[NotNull(message: "You must choose a supporter visibility.")]
    public ?bool $supporterVisible;
}
