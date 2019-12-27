<?php

namespace App\Tests\Controller\BackOffice\Funding;

use App\Entity\User;
use App\Tests\WebTestCase;

class ShipTransformDeleteControllerTest extends WebTestCase
{
    /** @var User */
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Ioni']);
    }

    /**
     * @group functional
     * @group ship_transform
     * @group bo
     */
    public function testNotAuth(): void
    {
        $this->client->request('POST', '/bo/ship-transform/delete/1a216d86-9fae-484c-ac0d-38804aad2039');

        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group ship_transform
     * @group bo
     */
    public function testNotAdmin(): void
    {
        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Gardien1']); // ROLE_USER
        $this->logIn($user);
        $this->client->request('POST', '/bo/ship-transform/delete/1a216d86-9fae-484c-ac0d-38804aad2039');

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group ship_transform
     * @group bo
     */
    public function testAdmin(): void
    {
        $this->logIn($this->user); // ROLE_ADMIN
        $this->client->request('POST', '/bo/ship-transform/delete/1a216d86-9fae-484c-ac0d-38804aad2039');

        $this->assertSame(302, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group ship_transform
     * @group bo
     */
    public function testNotExist(): void
    {
        $this->logIn($this->user); // ROLE_ADMIN
        $this->client->request('POST', '/bo/ship-transform/delete/3a6c50db-c204-4df2-91a0-a8209483f128'); // fake uuid

        $this->assertSame(302, $this->client->getResponse()->getStatusCode());
    }
}
