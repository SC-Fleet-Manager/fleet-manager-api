<?php

namespace App\Tests\Controller\BackOffice\Funding;

use App\Entity\User;
use App\Tests\WebTestCase;

class ShipChassisDeleteControllerTest extends WebTestCase
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
     * @group ship_chassis
     * @group bo
     */
    public function testNotAuth(): void
    {
        $this->client->request('POST', '/bo/ship-chassis/delete/a4f47e08-ac5d-48b4-a080-cca7ab260391');

        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group ship_chassis
     * @group bo
     */
    public function testNotAdmin(): void
    {
        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Gardien1']); // ROLE_USER
        $this->logIn($user);
        $this->client->request('POST', '/bo/ship-chassis/delete/a4f47e08-ac5d-48b4-a080-cca7ab260391');

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group ship_chassis
     * @group bo
     */
    public function testAdmin(): void
    {
        $this->logIn($this->user); // ROLE_ADMIN
        $this->client->request('POST', '/bo/ship-chassis/delete/a4f47e08-ac5d-48b4-a080-cca7ab260391');

        $this->assertSame(302, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group ship_chassis
     * @group bo
     */
    public function testNotExist(): void
    {
        $this->logIn($this->user); // ROLE_ADMIN
        $this->client->request('POST', '/bo/ship-chassis/delete/c0d1adef-53aa-47aa-b761-268af23ddfe8'); // fake uuid

        $this->assertSame(302, $this->client->getResponse()->getStatusCode());
    }
}
