<?php

namespace App\Domain;

use Symfony\Component\Serializer\Annotation\Groups;

class ShipInfo
{
    public const FLIGHT_READY = 'ready';
    public const NOT_READY = 'not_ready';

    public const SIZE_VEHICLE = 'vehicle';
    public const SIZE_SNUB = 'snub';
    public const SIZE_SMALL = 'small';
    public const SIZE_MEDIUM = 'medium';
    public const SIZE_LARGE = 'large';
    public const SIZE_CAPITAL = 'capital';
    public const SIZES = [
        self::SIZE_VEHICLE,
        self::SIZE_SNUB,
        self::SIZE_SMALL,
        self::SIZE_MEDIUM,
        self::SIZE_LARGE,
        self::SIZE_CAPITAL,
    ];

    /**
     * @Groups({"my-fleet", "public-fleet"})
     */
    public ?string $id = null;

    /**
     * @see self::FLIGHT_READY|self::NOT_READY
     *
     * @Groups({"my-fleet", "public-fleet"})
     */
    public ?string $productionStatus = null;

    /**
     * @Groups({"my-fleet", "public-fleet"})
     */
    public ?int $minCrew = null;

    /**
     * @Groups({"my-fleet", "public-fleet"})
     */
    public ?int $maxCrew = null;

    /**
     * @Groups({"my-fleet", "public-fleet"})
     */
    public ?string $name = null;

    /**
     * @see self::SIZES
     *
     * @Groups({"my-fleet", "public-fleet"})
     */
    public ?string $size = null;

    /**
     * @Groups({"my-fleet", "public-fleet"})
     */
    public ?int $cargoCapacity = null;

    /**
     * @Groups({"my-fleet", "public-fleet"})
     */
    public ?string $pledgeUrl = null;

    /**
     * @Groups({"my-fleet", "public-fleet"})
     */
    public ?string $manufacturerId = null;

    /**
     * @Groups({"my-fleet", "public-fleet"})
     */
    public ?string $manufacturerName = null;

    /**
     * @Groups({"my-fleet", "public-fleet"})
     */
    public ?string $manufacturerCode = null;

    /**
     * @Groups({"my-fleet", "public-fleet"})
     */
    public ?string $chassisId = null;

    /**
     * @Groups({"my-fleet", "public-fleet"})
     */
    public ?string $chassisName = null;

    /**
     * @Groups({"my-fleet", "public-fleet"})
     */
    public string $mediaUrl = '/build/images/static/placeholder_ship.svg';

    /**
     * @Groups({"my-fleet", "public-fleet"})
     */
    public string $mediaThumbUrl = '/build/images/static/placeholder_ship.svg';
}
