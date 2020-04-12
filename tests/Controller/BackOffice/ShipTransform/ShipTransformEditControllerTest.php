<?php

namespace App\Tests\Controller\BackOffice\ShipTransform;

use App\Entity\User;
use App\Tests\WebTestCase;

class ShipTransformEditControllerTest extends WebTestCase
{
    /** @var User */
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Ioni']);
    }

    /**
     * @group functional
     * @group ship_transform
     * @group bo
     */
    public function testNotAuth(): void
    {
        $this->client->request('GET', '/bo/ship-transform/edit/1a216d86-9fae-484c-ac0d-38804aad2039');

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
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Gardien1']); // ROLE_USER
        $this->logIn($user);
        $this->client->request('GET', '/bo/ship-transform/edit/1a216d86-9fae-484c-ac0d-38804aad2039');

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
        $this->client->request('GET', '/bo/ship-transform/edit/1a216d86-9fae-484c-ac0d-38804aad2039');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group ship_transform
     * @group bo
     */
    public function testNotExist(): void
    {
        $this->logIn($this->user); // ROLE_ADMIN
        $this->client->request('GET', '/bo/ship-transform/edit/3a6c50db-c204-4df2-91a0-a8209483f128'); // fake uuid

        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
    }
}
