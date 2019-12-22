<?php

namespace App\Form\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ProfilePreferences
{
    /**
     * @Assert\NotBlank(message="You must choose a fleet policy.")
     */
    public ?string $publicChoice;

    /**
     * @Assert\NotNull(message="You must choose a supporter visibility.")
     */
    public ?bool $supporterVisible;

    public ?array $orgaVisibilityChoices;

    public function __construct(?string $publicChoice = null, ?bool $supporterVisible = null, ?array $orgaVisibilityChoices = null)
    {
        $this->publicChoice = $publicChoice;
        $this->supporterVisible = $supporterVisible;
        $this->orgaVisibilityChoices = $orgaVisibilityChoices;
    }

    public function getOrgaVisibilityChoices(): array
    {
        return $this->orgaVisibilityChoices ?? [];
    }
}
