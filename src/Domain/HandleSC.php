<?php

namespace App\Domain;

class HandleSC
{
    /**
     * @var string
     */
    private $handle;

    public function __construct(string $handle)
    {
        $this->handle = $handle;
    }

    public function __toString()
    {
        return $this->handle;
    }
}
