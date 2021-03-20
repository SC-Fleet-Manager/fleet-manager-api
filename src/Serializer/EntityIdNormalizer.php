<?php

namespace App\Serializer;

use App\Domain\EntityId;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Uid\Ulid;

class EntityIdNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    public function normalize($object, string $format = null, array $context = []): string
    {
        return (string) $object;
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof EntityId;
    }

    public function denormalize($data, string $type, string $format = null, array $context = []): EntityId
    {
        return new $type(Ulid::fromString($data));
    }

    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return is_a($type, EntityId::class, true);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return __CLASS__ === static::class;
    }

    public static function getDefaultPriority(): int
    {
        return -880; // just before UidNormalizer
    }
}
