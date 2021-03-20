<?php

namespace App\Tests\Controller\BackOffice\Funding;

use App\Tests\WebTestCase;

class MonthlyCostCoverageListControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group funding
     * @group bo
     */
    public function test_not_auth(): void
    {
        $this->client->request('GET', '/bo/monthly-cost-coverage/list');

        static::assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group funding
     * @group bo
     */
    public function test_not_admin(): void
    {
        $this->client->request('GET', '/bo/monthly-cost-coverage/list', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Gardien1'),
        ]);

        static::assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group funding
     * @group bo
     */
    public function test_admin(): void
    {
        $crawler = $this->client->request('GET', '/bo/monthly-cost-coverage/list', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(200, $this->client->getResponse()->getStatusCode());
        static::assertSame('Monthly Cost Coverage List', $crawler->filter('h1')->text(null, false));
        static::assertStringContainsString('Default monthly cost coverage target', $crawler->filter('.container')->text(null, false));
    }
}
