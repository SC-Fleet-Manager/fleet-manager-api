<?php

namespace App\Form\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class PatchNote
{
    /**
     * @Assert\NotBlank()
     */
    public ?string $title = null;

    /**
     * @Assert\NotBlank()
     */
    public ?string $body = null;

    /**
     * @Assert\Url()
     */
    public ?string $link = null;

    public function __construct(?string $title = null, ?string $body = null, ?string $link = null)
    {
        $this->title = $title;
        $this->body = $body;
        $this->link = $link;
    }
}
