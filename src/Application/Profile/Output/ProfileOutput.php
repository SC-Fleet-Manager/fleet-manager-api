<?php

namespace App\Application\Profile\Output;

use App\Domain\UserId;
use OpenApi\Annotations as OpenApi;

class ProfileOutput
{
    public function __construct(
        /**
         * @OpenApi\Property(type="string", format="uid", example="00000000-0000-0000-0000-000000000001")
         */
        public UserId $id,
        /**
         * @OpenApi\Property(type="string", description="The internal username on Auth0. Used to authenticate the user.", example="auth0|abcdefgh12345678")
         */
        public string $auth0Username,
        /**
         * @OpenApi\Property(type="string", nullable=true, description="The user's nickname given by Auth0.", example="Ioni")
         */
        public ?string $nickname,
        /**
         * @OpenApi\Property(type="string", nullable=true, example="ioni14")
         */
        public ?string $handle,
        /**
         * @OpenApi\Property(type="boolean", description="true if the user wants to appear in the supporters list.")
         */
        public bool $supporterVisible,
        /**
         * @OpenApi\Property(type="integer", description="The user's balance of FM coins (FM currency)", example=100)
         */
        public int $coins,
        /**
         * @OpenApi\Property(type="string", format="date-time")
         */
        public \DateTimeInterface $createdAt,
    ) {
    }
}
