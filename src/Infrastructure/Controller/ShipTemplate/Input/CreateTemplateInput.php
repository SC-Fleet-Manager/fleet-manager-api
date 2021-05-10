<?php

namespace App\Infrastructure\Controller\ShipTemplate\Input;

use App\Entity\ShipSize;
use OpenApi\Annotations as OpenApi;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Valid;

class CreateTemplateInput
{
    public const SIZE_VEHICLE = 'vehicle';
    public const SIZE_SNUB = 'snub';
    public const SIZE_SMALL = 'small';
    public const SIZE_MEDIUM = 'medium';
    public const SIZE_LARGE = 'large';
    public const SIZE_CAPITAL = 'capital';

    private const SIZES = [self::SIZE_VEHICLE, self::SIZE_SNUB, self::SIZE_SMALL, self::SIZE_MEDIUM, self::SIZE_LARGE, self::SIZE_CAPITAL];

    /**
     * @OpenApi\Property(type="string", nullable=false, minLength=2, maxLength=60, example="Avenger Titan")
     */
    #[NotBlank]
    #[Length(min: 2, max: 60)]
    public ?string $model = null;

    /**
     * @OpenApi\Property(type="string", format="url", nullable=true, example="https://media.robertsspaceindustries.com/fmhdkmvhi8ify/store_small.jpg")
     */
    #[NotBlank(allowNull: true)]
    #[Regex(
        pattern: '~^https://((media.)?robertsspaceindustries.com|(www.)?starcitizen.tools)/~',
        message: 'The picture URL must come from robertsspaceindustries.com or starcitizen.tools.'
    )]
    public ?string $pictureUrl = null;

    #[Valid]
    public CreateTemplateChassisInput $chassis;

    #[Valid]
    public CreateTemplateManufacturerInput $manufacturer;

    /**
     * @OpenApi\Property(type="string", nullable=true)
     */
    #[Choice(self::SIZES)]
    public ?string $size = null;

    /**
     * @OpenApi\Property(type="string", nullable=true, example="Combat")
     */
    #[Length(min: 3, max: 30)]
    public ?string $role = null;

    /**
     * @OpenApi\Property(type="integer", nullable=true)
     */
    #[range(min: 0, max: 2_000_000_000)]
    public mixed $cargoCapacity = null;

    #[Valid]
    public CreateTemplateCrewInput $crew;

    #[Valid]
    public CreateTemplatePriceInput $price;

    public function __construct()
    {
        $this->chassis = new CreateTemplateChassisInput();
        $this->manufacturer = new CreateTemplateManufacturerInput();
        $this->crew = new CreateTemplateCrewInput();
        $this->price = new CreateTemplatePriceInput();
    }

    public function createShipSize(): ShipSize
    {
        switch ($this->size) {
            case self::SIZE_VEHICLE:
                return ShipSize::vehicle();
            case self::SIZE_SNUB:
                return ShipSize::snub();
            case self::SIZE_SMALL:
                return ShipSize::small();
            case self::SIZE_MEDIUM:
                return ShipSize::medium();
            case self::SIZE_LARGE:
                return ShipSize::large();
            case self::SIZE_CAPITAL:
                return ShipSize::capital();
        }

        return ShipSize::unknown();
    }
}
