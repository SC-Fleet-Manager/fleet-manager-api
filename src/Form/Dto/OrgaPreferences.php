<?php

namespace App\Form\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class OrgaPreferences
{
    /**
     * @Assert\NotBlank(message="You must choose a fleet policy.")
     * @Assert\Choice(choices=App\Entity\Organization::PUBLIC_CHOICES, message="You must select a valid option.")
     */
    public ?string $publicChoice;

    /**
     * @Assert\NotNull(message="You must choose a supporter visibility.")
     */
    public ?bool $supporterVisible;

    public function __construct(?string $publicChoice = null, ?bool $supporterVisible = null)
    {
        $this->publicChoice = $publicChoice;
        $this->supporterVisible = $supporterVisible;
    }
}
