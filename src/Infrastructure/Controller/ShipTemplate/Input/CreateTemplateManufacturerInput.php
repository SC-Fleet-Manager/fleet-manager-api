<?php

namespace App\Infrastructure\Controller\ShipTemplate\Input;

use OpenApi\Annotations as OpenApi;
use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function Symfony\Component\String\u;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;

class CreateTemplateManufacturerInput implements DenormalizableInterface
{
    /**
     * @OpenApi\Property(type="string", nullable=true, minLength=2, maxLength=60, example="Robert Space Industries")
     */
    #[Length(min: 3, max: 50)]
    public ?string $name = null;
    /**
     * @OpenApi\Property(type="string", nullable=true, minLength=3, maxLength=5, example="RSI")
     */
    #[Regex(pattern: '~^[a-zA-Z]{3,5}$~', message: 'Manufacturer code must contain only 3 to 5 letters.')]
    public ?string $code = null;

    public function denormalize(DenormalizerInterface $denormalizer, $data, string $format = null, array $context = []): void
    {
        if (isset($data['name'])) {
            $this->name = u($data['name'])->trim();
        }
        if (isset($data['code'])) {
            $this->code = u($data['code'])->trim()->upper();
        }
    }
}
