<?php

declare(strict_types=1);

namespace App\Domain;

class ShipInfo
{
    public const FLIGHT_READY = 'ready';
    public const NOT_READY = 'not_ready';

    /**
     * @var string
     */
    public $id;

    /**
     * @var string self::FLIGHT_READY|self::NOT_READY
     */
    public $productionStatus;

    /**
     * @var int
     */
    public $minCrew;

    /**
     * @var int
     */
    public $maxCrew;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $pledgeUrl;

    /**
     * @var string
     */
    public $manufacturerName;

    /**
     * @var string
     */
    public $manufacturerCode;

    /**
     * @var string
     */
    public $mediaUrl;

    /**
     * @var string
     */
    public $mediaThumbUrl;
}
