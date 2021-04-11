<?php

namespace App\Infrastructure\Controller\Profile\Input;

use OpenApi\Annotations as OpenApi;
use Symfony\Component\Validator\Constraints\Length;

class ChangeNicknameInput
{
    /**
     * @OpenApi\Property(type="string", nullable=true, minLength=2, maxLength=31, example="Ioni")
     */
    #[Length(
        min: 2,
        max: 31,
        minMessage: 'The nickname must have 2 characters or more.',
        maxMessage: 'The nickname must have 31 characters or less.')
    ]
    public ?string $nickname = null;
}
