<?php

namespace App\Tests\Controller\BackOffice\Funding;

use App\Entity\User;
use App\Tests\WebTestCase;

class MonthlyCostCoverageEditControllerTest extends WebTestCase
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
     * @group funding
     * @group bo
     */
    public function testNotAuth(): void
    {
        $this->client->request('GET', '/bo/monthly-cost-coverage/edit/0a77f3fc-3ecd-48f5-b9f6-dfb8ac5ed642');

        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
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
        $this->client->request('GET', '/bo/monthly-cost-coverage/edit/0a77f3fc-3ecd-48f5-b9f6-dfb8ac5ed642');

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group funding
     * @group bo
     */
    public function testAdmin(): void
    {
        $this->logIn($this->user); // ROLE_ADMIN
        $this->client->request('GET', '/bo/monthly-cost-coverage/edit/0a77f3fc-3ecd-48f5-b9f6-dfb8ac5ed642');

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group funding
     * @group bo
     */
    public function testNotExist(): void
    {
        $this->logIn($this->user); // ROLE_ADMIN
        $this->client->request('GET', '/bo/monthly-cost-coverage/edit/624efc18-d5ee-445f-9448-6803b57924aa'); // fake uuid

        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group funding
     * @group bo
     */
    public function testLastMonthUneditable(): void
    {
        $this->logIn($this->user); // ROLE_ADMIN
        $this->client->request('GET', '/bo/monthly-cost-coverage/edit/d427d39d-f1d0-4ece-b1fe-0a9e80735b76'); // last month (uneditable)

        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
    }
}
