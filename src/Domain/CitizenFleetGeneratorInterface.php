<?php

namespace App\Domain;

interface CitizenFleetGeneratorInterface
{
    public function generateFleetFile(CitizenNumber $number): \SplFileInfo;
}
