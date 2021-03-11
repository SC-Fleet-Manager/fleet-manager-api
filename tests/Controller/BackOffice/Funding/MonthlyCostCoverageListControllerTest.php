<?php

namespace App\Tests\Controller\BackOffice\Funding;

use App\Entity\User;
use App\Tests\WebTestCase;

class MonthlyCostCoverageListControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group funding
     * @group bo
     */
    public function testNotAuth(): void
    {
        $this->client->request('GET', '/bo/monthly-cost-coverage/list');

        static::assertSame(401, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group funding
     * @group bo
     */
    public function testNotAdmin(): void
    {
        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Gardien1']); // ROLE_USER
        $this->logIn($user);
        $this->client->request('GET', '/bo/monthly-cost-coverage/list');

        static::assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group funding
     * @group bo
     */
    public function testAdmin(): void
    {
        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Ioni']); // ROLE_ADMIN
        $this->logIn($user);
        $crawler = $this->client->request('GET', '/bo/monthly-cost-coverage/list');

        static::assertSame(200, $this->client->getResponse()->getStatusCode());
        static::assertSame('Monthly Cost Coverage List', $crawler->filter('h1')->text(null, false));
        static::assertStringContainsString('Default monthly cost coverage target', $crawler->filter('.container')->text(null, false));
    }
}
