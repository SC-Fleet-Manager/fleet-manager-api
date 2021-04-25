<?php

namespace App\Infrastructure\Controller\MyOrganizations\Input;

use OpenApi\Annotations as OpenApi;
use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function Symfony\Component\String\u;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class UpdateOrganizationInput implements DenormalizableInterface
{
    /**
     * @OpenApi\Property(type="string", nullable=false, minLength=3, maxLength=32, example="Force Coloniale Unifiée")
     */
    #[NotBlank]
    #[Length(min: 3, max: 32)]
    public ?string $name = null;

    /**
     * @OpenApi\Property(type="string", format="url", nullable=true, example="https://robertsspaceindustries.com/media/p7en31fqpos97r/logo/FCU-Logo.png")
     */
    #[NotBlank(allowNull: true)]
    #[Regex(
        pattern: '~^https://(media.)?robertsspaceindustries.com/~',
        message: 'The logo URL must come from robertsspaceindustries.com.'
    )]
    public ?string $logoUrl = null;

    public function denormalize(DenormalizerInterface $denormalizer, $data, string $format = null, array $context = []): void
    {
        $this->name = $data['name'] ?? null;
        if ($this->name !== null) {
            $this->name = u($this->name)->trim();
        }
        $this->logoUrl = $data['logoUrl'] ?? null;
    }
}
