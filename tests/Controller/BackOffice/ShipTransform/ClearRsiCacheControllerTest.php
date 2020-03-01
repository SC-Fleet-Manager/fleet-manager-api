<?php

namespace App\Tests\Controller\BackOffice\Funding;

use App\Entity\User;
use App\Tests\WebTestCase;

class ClearRsiCacheControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group ship_transform
     * @group bo
     */
    public function testNotAuth(): void
    {
        $this->client->request('POST', '/bo/clear-rsi-cache');

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
        $this->client->request('POST', '/bo/clear-rsi-cache');

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
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Ioni']); // ROLE_ADMIN
        $this->logIn($user);
        $this->client->request('POST', '/bo/clear-rsi-cache');

        $this->assertSame(302, $this->client->getResponse()->getStatusCode());
    }
}
