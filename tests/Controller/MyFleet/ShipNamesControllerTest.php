<?php

namespace App\Tests\Controller\MyFleet;

use App\Tests\WebTestCase;

class ShipNamesControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group api
     */
    public function testIndex(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/ship-names', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(1, $json['shipNames']);
        $this->assertArraySubset([
            'HangarShipName1' => [
                'myHangarName' => 'HangarShipName1',
                'shipMatrixName' => 'MatrixShipName1',
            ],
        ], $json['shipNames']);
    }
}
