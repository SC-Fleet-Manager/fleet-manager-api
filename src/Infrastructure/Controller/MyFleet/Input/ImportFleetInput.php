<?php

namespace App\Infrastructure\Controller\MyFleet\Input;

use App\Application\MyFleet\Input\ImportFleetShip;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use function Symfony\Component\String\u;
use Symfony\Component\Validator\Constraints\NotNull;

class ImportFleetInput implements DenormalizableInterface
{
    /**
     * @var ImportFleetShip[]
     * @OpenApi\Property(type="string", nullable=false, description="JSON-encoded content of the Hangar Transfer Format file.")
     */
    #[NotNull(message: 'Unable to decode your file. Check it contains valid JSON.')]
    public ?array $hangarExplorerContent = [];

    public bool $onlyMissing = false;

    public function denormalize(DenormalizerInterface $denormalizer, $data, string $format = null, array $context = []): void
    {
        $this->onlyMissing = (bool) ($data['onlyMissing'] ?? false);
        $shipsData = [];
        if (isset($data['hangarExplorerContent'])) {
            try {
                $shipsData = json_decode($data['hangarExplorerContent'], true, flags: JSON_THROW_ON_ERROR);
            } catch (\Throwable) {
                $this->hangarExplorerContent = null;
                return;
            }
        }
        $this->hangarExplorerContent = [];
        foreach ($shipsData as $shipData) {
            if (!isset($shipData['name'])) {
                continue;
            }
            $this->hangarExplorerContent[] = new ImportFleetShip(u($shipData['name'])->trim()->toString());
        }
    }
}
