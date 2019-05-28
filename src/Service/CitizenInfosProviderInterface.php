<?php

namespace App\Service;

use App\Domain\CitizenInfos;
use App\Domain\HandleSC;

interface CitizenInfosProviderInterface
{
    public function retrieveInfos(HandleSC $handleSC, bool $caching = true): CitizenInfos;
}
