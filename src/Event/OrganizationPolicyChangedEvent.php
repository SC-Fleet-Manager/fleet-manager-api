<?php

namespace App\Event;

use App\Entity\Citizen;
use App\Entity\Organization;
use Symfony\Contracts\EventDispatcher\Event;

class OrganizationPolicyChangedEvent extends Event
{
    private Citizen $author;
    private Organization $organization;
    /** @see Organization::PUBLIC_CHOICES */
    private string $oldPolicy;
    /** @see Organization::PUBLIC_CHOICES */
    private string $newPolicy;

    public function __construct(Citizen $author, Organization $organization, string $oldPolicy, string $newPolicy)
    {
        $this->author = $author;
        $this->organization = $organization;
        $this->oldPolicy = $oldPolicy;
        $this->newPolicy = $newPolicy;
    }

    public function getAuthor(): Citizen
    {
        return $this->author;
    }

    public function getOrganization(): Organization
    {
        return $this->organization;
    }

    public function getOldPolicy(): string
    {
        return $this->oldPolicy;
    }

    public function getNewPolicy(): string
    {
        return $this->newPolicy;
    }
}
