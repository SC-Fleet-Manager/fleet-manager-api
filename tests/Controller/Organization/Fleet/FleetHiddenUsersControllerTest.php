<?php

namespace App\Tests\Controller\Organization\Fleet;

use App\Entity\User;
use App\Tests\WebTestCase;

class FleetHiddenUsersControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group organization_fleet
     */
    public function testOrgaFleetsHiddenUsers(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Pulsar42Admin']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/pulsar42/hidden-users/cbcb60c7-a780-4a59-b51d-0ad8021813bf', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(1, $json['hiddenUsers']); // Pulsar42Member1
        $this->assertFalse($json['citizenFiltered']);
    }

    /**
     * @group functional
     * @group organization_fleet
     */
    public function testOrgaFleetsHiddenUsersWithFilteredCitizens(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Pulsar42Admin']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/fleet/orga-fleets/pulsar42/hidden-users/cbcb60c7-a780-4a59-b51d-0ad8021813bf', [
            'filters' => [
                'citizenIds' => [
                    '3c201cad-860e-4e7b-a072-0b496bb97464', // pulsar42_member3
                ],
            ],
        ], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame(1, $json['hiddenUsers']); // Pulsar42Member1
        $this->assertTrue($json['citizenFiltered']);
    }
}
