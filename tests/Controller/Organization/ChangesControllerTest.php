<?php

namespace App\Tests\Controller\Organization;

use App\Entity\Citizen;
use App\Entity\User;
use App\Service\Citizen\InfosProvider\CitizenInfosProviderInterface;
use App\Tests\WebTestCase;

class ChangesControllerTest extends WebTestCase
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
     * @group organization
     */
    public function testChangesUpdateFleet(): void
    {
        $jsonContent = <<<EOT
                [
                  {
                    "manufacturer": "RSI",
                    "name": "Orion",
                    "lti": true,
                    "warbond": true,
                    "package_id": "15109407",
                    "pledge": "Standalone Orion",
                    "pledge_date": "April 28, 2018",
                    "cost": "$110.00 USD"
                  }
                ]
            EOT;

        $citizenInfosProvider = static::$container->get(CitizenInfosProviderInterface::class);
        $citizenInfosProvider->setCitizen($this->user->getCitizen());

        $this->client->xmlHttpRequest('POST', '/api/export', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer '.$this->user->getApiToken(),
            'CONTENT_TYPE' => 'application/json',
        ], $jsonContent);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());

        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Ashuvidz']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/organization/flk/changes', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            [
                'type' => 'upload_fleet',
                'payload' => [
                    [
                        'shipGalaxyId' => 'e37c618b-3ec6-4d4d-92b6-5aed679962a2',
                        'ship' => 'Cutlass Black',
                        'manu' => 'Drake',
                        'count' => -1,
                    ],
                    [
                        'shipGalaxyId' => '9950adb5-9151-4760-9073-080416120fca',
                        'manu' => 'RSI',
                        'ship' => 'Orion',
                        'count' => 1,
                    ],
                ],
                'author' => [
                    'id' => '7275c744-6a69-43c2-9ebf-1491a104d5e7',
                    'actualHandle' => [
                        'handle' => 'ionni',
                    ],
                ],
            ],
        ], $json);
    }

    /**
     * @group functional
     * @group organization
     */
    public function testChangesJoinOrga(): void
    {
        /** @var User $userNeedRefresh */
        $userNeedRefresh = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'needRefresh']);
        $this->logIn($userNeedRefresh);
        $this->client->xmlHttpRequest('POST', '/api/profile/refresh-rsi-profile', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertSame(204, $this->client->getResponse()->getStatusCode());

        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Ashuvidz']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/organization/flk/changes', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            [
                'type' => 'join_orga',
                'payload' => [],
                'author' => [
                    'id' => '4498ab8d-39ba-4bf3-bea9-77bf7464feae',
                    'actualHandle' => [
                        'handle' => 'need_refresh',
                    ],
                ],
            ],
        ], $json);
    }

    /**
     * @group functional
     * @group organization
     */
    public function testChangesLeaveOrga(): void
    {
        $citizen = new Citizen(); // no orgas
        $citizen->setActualHandle(clone $this->user->getCitizen()->getActualHandle());
        $citizen->setNickname($this->user->getCitizen()->getNickname());
        $citizen->setNumber(clone $this->user->getCitizen()->getNumber());

        $citizenInfosProvider = static::$container->get(CitizenInfosProviderInterface::class);
        $citizenInfosProvider->setCitizen($citizen);
        $citizenInfosProvider->setKnownCitizens([]);

        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/profile/refresh-rsi-profile', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertSame(204, $this->client->getResponse()->getStatusCode());

        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'Ashuvidz']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/organization/flk/changes', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            [
                'type' => 'leave_orga',
                'payload' => [],
                'author' => [
                    'id' => '7275c744-6a69-43c2-9ebf-1491a104d5e7',
                    'actualHandle' => [
                        'handle' => 'ionni',
                    ],
                ],
            ],
        ], $json);
    }

    /**
     * @group functional
     * @group organization
     */
    public function testChangesForbidden(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/organization/flk/changes', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights', $json['error']);
    }

    /**
     * @group functional
     * @group organization
     */
    public function testChangesNotAuth(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/organization/flk/changes', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_auth', $json['error']);
    }
}
