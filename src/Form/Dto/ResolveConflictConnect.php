<?php

namespace App\Form\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @Assert\GroupSequence({"ResolveConflictConnect", "first_pass", "second_pass"})
 */
class ResolveConflictConnect
{
    /**
     * @var string|null
     *
     * @Assert\NotBlank(message="You must choose the Citizen you want to keep.", groups={"first_pass"})
     * @Assert\Choice(callback="getConflictChoices", message="You must choose a Citizen among the proposals.", groups={"second_pass"})
     */
    public $conflictChoice;

    /** @var string[] */
    private $conflictChoices;

    public function getConflictChoices(): array
    {
        return $this->conflictChoices;
    }

    public function setConflictChoices(array $choices): void
    {
        $this->conflictChoices = $choices;
    }
}
