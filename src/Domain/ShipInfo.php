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
     * @var string
     *
     * @Groups({"my-fleet", "public-fleet"})
     */
    public $id;

    /**
     * @var string self::FLIGHT_READY|self::NOT_READY
     *
     * @Groups({"my-fleet", "public-fleet"})
     */
    public $productionStatus;

    /**
     * @var int
     * @Groups({"my-fleet", "public-fleet"})
     */
    public $minCrew;

    /**
     * @var int
     *
     * @Groups({"my-fleet", "public-fleet"})
     */
    public $maxCrew;

    /**
     * @var string
     *
     * @Groups({"my-fleet", "public-fleet"})
     */
    public $name;

    /**
     * @var string self::SIZES
     *
     * @Groups({"my-fleet", "public-fleet"})
     */
    public $size;

    /**
     * @var int
     *
     * @Groups({"my-fleet", "public-fleet"})
     */
    public $cargoCapacity;

    /**
     * @var string
     *
     * @Groups({"my-fleet", "public-fleet"})
     */
    public $pledgeUrl;

    /**
     * @var string
     *
     * @Groups({"my-fleet", "public-fleet"})
     */
    public $manufacturerName;

    /**
     * @var string
     *
     * @Groups({"my-fleet", "public-fleet"})
     */
    public $manufacturerCode;

    /**
     * @var string
     *
     * @Groups({"my-fleet", "public-fleet"})
     */
    public $chassisId;

    /**
     * @var string
     *
     * @Groups({"my-fleet", "public-fleet"})
     */
    public $chassisName;

    /**
     * @var string
     *
     * @Groups({"my-fleet", "public-fleet"})
     */
    public $mediaUrl;

    /**
     * @var string
     *
     * @Groups({"my-fleet", "public-fleet"})
     */
    public $mediaThumbUrl;
}
