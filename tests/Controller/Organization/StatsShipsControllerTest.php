<?php

namespace App\Tests\Controller\Organization;

use App\Entity\User;
use App\Tests\WebTestCase;

class StatsShipsControllerTest extends WebTestCase
{
    /** @var User */
    private $user;

    public function setUp(): void
    {
        parent::setUp();
        $this->user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Ioni']);
    }

    /**
     * @group functional
     * @group organization
     */
    public function testStats(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/organization/flk/stats/ships', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            'countShips' => 7,
            'countFlightReady' => 5,
            'countInConcept' => 2,
            'minCrew' => 14,
            'maxCrew' => 19,
            'cargoCapacity' => 0,
            'chartShipSizes' => [
                'xAxis' => [
                    0 => 'vehicle',
                    1 => 'snub',
                    2 => 'small',
                    3 => 'medium',
                    4 => 'large',
                    5 => 'capital',
                ],
                'yAxis' => [
                    0 => 1,
                    1 => 1,
                    2 => 1,
                    3 => 2,
                    4 => 1,
                    5 => 1,
                ],
            ],
        ], $json);
    }

    /**
     * @group functional
     * @group organization
     */
    public function testStatsForbidden(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/organization/flk/stats/ships', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_public', $json['error']);
    }
}
