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
    public function test_index(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/funding/progress', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        static::assertSame(6312, $json['progress']);
        static::assertSame(15000, $json['target']);
    }

    /**
     * @group functional
     * @group funding
     */
    public function test_postpone(): void
    {
        $lastMonthCoverage = $this->doctrine->getRepository(MonthlyCostCoverage::class)->findOneBy(['month' => new \DateTimeImmutable('first day of last month')]);
        $lastMonthCoverage->setPostpone(true);
        $this->doctrine->getManager()->flush();

        $this->client->xmlHttpRequest('GET', '/api/funding/progress', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        static::assertSame(29352, $json['target']);
    }

    /**
     * @group functional
     * @group funding
     */
    public function test_default(): void
    {
        $currentMonthCoverage = $this->doctrine->getRepository(MonthlyCostCoverage::class)->findOneBy(['month' => new \DateTimeImmutable('first day of')]);
        $this->doctrine->getManager()->remove($currentMonthCoverage);
        $this->doctrine->getManager()->flush();

        $this->client->xmlHttpRequest('GET', '/api/funding/progress', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        static::assertSame(10000, $json['target']);
    }
}
