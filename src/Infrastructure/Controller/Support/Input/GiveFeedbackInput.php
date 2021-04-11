<?php

namespace App\Infrastructure\Controller\Support\Input;

use OpenApi\Annotations as OpenApi;
use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use function Symfony\Component\String\u;

class GiveFeedbackInput implements DenormalizableInterface
{
    /**
     * @OpenApi\Property(type="string", nullable=false)
     */
    #[NotBlank]
    #[Length(min: 10, max: 1023)]
    public ?string $description;

    /**
     * @OpenApi\Property(type="string", format="email", nullable=true)
     */
    #[Email]
    public ?string $email;

    /**
     * @OpenApi\Property(type="string", nullable=true)
     */
    public ?string $discordId;

    public function denormalize(DenormalizerInterface $denormalizer, $data, string $format = null, array $context = []): void
    {
        $this->description = $data['description'] ?? null;
        if ($this->description !== null) {
            $this->description = u($this->description)->trim();
        }
        $this->email = $data['email'] ?? null;
        $this->discordId = $data['discordId'] ?? null;
    }
}
