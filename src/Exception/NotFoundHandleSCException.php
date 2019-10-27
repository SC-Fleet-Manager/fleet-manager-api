<?php

namespace App\Exception;

use App\Domain\HandleSC;

class NotFoundHandleSCException extends \RuntimeException
{
    public $handleSC;

    public function __construct(HandleSC $handleSC)
    {
        parent::__construct(sprintf('Handle %s does not exist', (string) $handleSC));
        $this->handleSC = $handleSC;
    }
}
