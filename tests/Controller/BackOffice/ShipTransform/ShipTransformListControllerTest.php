<?php

namespace App\Tests\Controller\BackOffice\Funding;

use App\Entity\User;
use App\Tests\WebTestCase;

class ShipTransformListControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group ship_transform
     * @group bo
     */
    public function testNotAuth(): void
    {
        $this->client->request('GET', '/bo/ship-transform/list');

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
        $this->client->request('GET', '/bo/ship-transform/list');

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group ship_transform
     * @group bo
     */
    public function testAdmin(): void
    {
        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Ioni']); // ROLE_ADMIN
        $this->logIn($user);
        $crawler = $this->client->request('GET', '/bo/ship-transform/list');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame('Ship Transform List', $crawler->filter('h1')->text());
    }
}
