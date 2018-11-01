<?php

namespace App\Infrastructure\Entity;

use Ramsey\Uuid\Uuid;

class Fleet
{
    /**
     * @var Uuid
     *
     *
     */
    public $id;
    public $ships;
    public $owner;
    public $uploadDate;
    public $version;
    public $file;
}
