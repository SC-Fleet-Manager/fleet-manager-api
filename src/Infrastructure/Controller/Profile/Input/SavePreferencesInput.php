<?php

namespace App\Infrastructure\Controller\Profile\Input;

use OpenApi\Annotations as OpenApi;
use Symfony\Component\Validator\Constraints\NotNull;

class SavePreferencesInput
{
    /**
     * @OpenApi\Property(type="boolean", description="true if the user wants to be visible in supporters list.")
     */
    #[NotNull(message: 'You must choose a supporter visibility.')]
    public ?bool $supporterVisible;
}
