<?php

namespace App\Domain;

use Symfony\Component\Serializer\Annotation\Groups;

class ShipInfo
{
    public const FLIGHT_READY = 'ready';
    public const NOT_READY = 'not_ready';

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
