<?php

namespace App\Tests\Controller\Funding;

use App\Entity\MonthlyCostCoverage;
use App\Tests\WebTestCase;

class ProgressControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group funding
     */
    public function testIndex(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/funding/progress', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(6312, $json['progress']);
        $this->assertSame(15000, $json['target']);
    }

    /**
     * @group functional
     * @group funding
     */
    public function testPostpone(): void
    {
        $lastMonthCoverage = $this->doctrine->getRepository(MonthlyCostCoverage::class)->findOneBy(['month' => new \DateTimeImmutable('first day of last month')]);
        $lastMonthCoverage->setPostpone(true);
        $this->doctrine->getManager()->flush();

        $this->client->xmlHttpRequest('GET', '/api/funding/progress', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(29352, $json['target']);
    }

    /**
     * @group functional
     * @group funding
     */
    public function testDefault(): void
    {
        $currentMonthCoverage = $this->doctrine->getRepository(MonthlyCostCoverage::class)->findOneBy(['month' => new \DateTimeImmutable('first day of')]);
        $this->doctrine->getManager()->remove($currentMonthCoverage);
        $this->doctrine->getManager()->flush();

        $this->client->xmlHttpRequest('GET', '/api/funding/progress', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(10000, $json['target']);
    }
}
