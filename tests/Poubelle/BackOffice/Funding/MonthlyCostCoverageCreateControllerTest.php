<?php

namespace App\Tests\Controller\BackOffice\Funding;

use App\Tests\WebTestCase;

class MonthlyCostCoverageCreateControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group funding
     * @group bo
     */
    public function test_not_auth(): void
    {
        $this->client->request('GET', '/bo/monthly-cost-coverage/create');

        static::assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group funding
     * @group bo
     */
    public function test_not_admin(): void
    {
        $this->client->request('GET', '/bo/monthly-cost-coverage/create', [], [], [
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
        $this->client->request('GET', '/bo/monthly-cost-coverage/create', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(200, $this->client->getResponse()->getStatusCode());
    }
}
