<?php

namespace App\Tests\Controller\BackOffice\Funding;

use App\Entity\User;
use App\Tests\WebTestCase;

class MonthlyCostCoverageEditControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group funding
     * @group bo
     */
    public function testNotAuth(): void
    {
        $this->client->request('GET', '/bo/monthly-cost-coverage/edit/0a77f3fc-3ecd-48f5-b9f6-dfb8ac5ed642');

        static::assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group funding
     * @group bo
     */
    public function testNotAdmin(): void
    {
        $this->client->request('GET', '/bo/monthly-cost-coverage/edit/0a77f3fc-3ecd-48f5-b9f6-dfb8ac5ed642', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Gardien1'),
        ]);

        static::assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group funding
     * @group bo
     */
    public function testAdmin(): void
    {
        $this->client->request('GET', '/bo/monthly-cost-coverage/edit/0a77f3fc-3ecd-48f5-b9f6-dfb8ac5ed642', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]);

        static::assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group funding
     * @group bo
     */
    public function testNotExist(): void
    {
        $this->client->request('GET', '/bo/monthly-cost-coverage/edit/624efc18-d5ee-445f-9448-6803b57924aa', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]); // fake uuid

        static::assertSame(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group funding
     * @group bo
     */
    public function testLastMonthUneditable(): void
    {
        $this->client->request('GET', '/bo/monthly-cost-coverage/edit/d427d39d-f1d0-4ece-b1fe-0a9e80735b76', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.static::generateToken('Ioni'),
        ]); // last month (uneditable)

        static::assertSame(404, $this->client->getResponse()->getStatusCode());
    }
}
