<?php

namespace App\Domain;

interface CitizenRepositoryInterface
{
    function getByHandle(HandleSC $handle): ?Citizen;

    function create(Citizen $citizen): void;
}
