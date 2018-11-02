<?php

namespace App\Domain;

interface CitizenInfosProviderInterface
{
    public function retrieveInfos(HandleSC $handleSC): CitizenInfos;
}
