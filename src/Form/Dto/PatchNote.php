<?php

namespace App\Form\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class PatchNote
{
    public function __construct(
        #[Assert\NotBlank]
        public ?string $title = null,

        #[Assert\NotBlank]
        public ?string $body = null,

        #[Assert\Url]
        public ?string $link = null
    ) {
    }
}
