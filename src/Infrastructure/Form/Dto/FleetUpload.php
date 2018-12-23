<?php

namespace App\Infrastructure\Form\Dto;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class FleetUpload
{
//    /**
//     * @var string
//     *
//     * @Assert\NotBlank(message="You must enter a Starcitizen handle.")
//     */
//    public $handleSC;

    /**
     * @var UploadedFile
     *
     * @Assert\NotBlank(message="You must upload a fleet file.")
     * @Assert\File(maxSize="5m")
     */
    public $fleetFile;
}
