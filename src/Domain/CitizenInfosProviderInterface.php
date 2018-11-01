<?php

namespace App\Domain;

interface CitizenInfosProviderInterface
{
    function retrieveInfos(HandleSC $handleSC): CitizenInfos;
}
