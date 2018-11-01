<?php

namespace App\Domain;

use Ramsey\Uuid\Uuid;

class Fleet
{
    /**
     * @var Uuid
     */
    private $id;

    /**
     * @var iterable|Ship[]
     */
    private $ships;

    /**
     * @var CitizenNumber
     */
    private $owner;

    /**
     * @var \DateTimeImmutable
     */
    private $uploadDate;

    /**
     * @var int
     */
    private $version;

    /**
     * @var \SplFileInfo
     */
    private $file;

    public function __construct($id)
    {
        $this->id = $id;
    }
}
