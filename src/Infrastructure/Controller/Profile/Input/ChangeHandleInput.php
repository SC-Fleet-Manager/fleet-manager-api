<?php

namespace App\Infrastructure\Controller\Profile\Input;

use App\Domain\UserId;
use App\Infrastructure\Validator\UniqueUserHandle;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\Serializer\Annotation\Ignore;
use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function Symfony\Component\String\u;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

#[UniqueUserHandle('handle', 'userId')]
class ChangeHandleInput implements DenormalizableInterface
{
    #[Ignore]
    public ?UserId $userId = null;

    /**
     * @OpenApi\Property(type="string", nullable=true, minLength=2, maxLength=31, example="ioni")
     */
    #[NotBlank]
    #[Regex(pattern: '~^[a-z0-9_-]{3,31}$~', message: 'The handle must contain only 3 to 31 letters (a-z) and/or digits.')]
    public ?string $handle = null;

    public function denormalize(DenormalizerInterface $denormalizer, $data, string $format = null, array $context = []): void
    {
        $this->handle = $data['handle'] ?? null;
        if ($this->handle !== null) {
            $this->handle = u($this->handle)->trim()->lower();
        }
    }
}
