<?php

namespace App\Event;

use App\Entity\Citizen;
use App\Entity\Organization;
use Symfony\Contracts\EventDispatcher\Event;

class OrganizationPolicyChangedEvent extends Event
{
    /**
     * @var Citizen
     */
    private $author;

    /**
     * @var Organization
     */
    private $organization;

    /**
     * @var string Organization::PUBLIC_CHOICES
     */
    private $oldPolicy;

    /**
     * @var string Organization::PUBLIC_CHOICES
     */
    private $newPolicy;

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
