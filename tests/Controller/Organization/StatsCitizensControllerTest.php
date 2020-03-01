<?php

namespace App\Tests\Controller\Organization;

use App\Entity\User;
use App\Tests\WebTestCase;

class StatsCitizensControllerTest extends WebTestCase
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
    public function testStats(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/organization/flk/stats/citizens', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            'countCitizens' => 3,
            'totalMembers' => 5,
            'averageShipsPerCitizen' => 3.5,
            'citizenMostShips' => [
                'citizen' => [
                    'id' => '08cc11ec-26ac-4638-8e03-c40b857d32bd',
                    'handle' => 'ihaveships',
                ],
                'countShips' => '6',
            ],
            'chartShipsPerCitizen' => [
                'xAxis' => [
                    0 => 1,
                    1 => 2,
                    2 => 3,
                    3 => 4,
                    4 => 5,
                    5 => 6,
                    6 => 7,
                    7 => 8,
                    8 => 9,
                    9 => 10,
                ],
                'yAxis' => [
                    0 => 1,
                    1 => 0,
                    2 => 0,
                    3 => 0,
                    4 => 0,
                    5 => 1,
                    6 => 0,
                    7 => 0,
                    8 => 0,
                    9 => 0,
                ],
            ],
        ], $json);
    }

    /**
     * @group functional
     * @group organization
     */
    public function testCitizenWithMostShipsAnonymousOrga(): void
    {
        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'orgaStats2']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/organization/orgastats/stats/citizens', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            'countCitizens' => 2,
            'totalMembers' => 5,
            'averageShipsPerCitizen' => 0.5,
            'citizenMostShips' => [
                'citizen' => [
                    'id' => '79245ea3-73c8-4d34-83e6-2adff81f417d',
                    'handle' => 'Anonymous', // orgaStats1 with orga visibility=private
                ],
                'countShips' => '1',
            ],
        ], $json);
    }

    /**
     * @group functional
     * @group organization
     */
    public function testCitizenWithMostShipsAnonymousPrivate(): void
    {
        $this->doctrine->getRepository(User::class)->createQueryBuilder('u')
            ->update('App:User', 'u')
            ->set('u.publicChoice', ':publicChoice')
            ->where('u.nickname = :nickname')
            ->setParameters([
                'nickname' => 'orgaStats1',
                'publicChoice' => User::PUBLIC_CHOICE_PRIVATE,
            ])
            ->getQuery()
            ->execute();
        $this->doctrine->getManager()->refresh($this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'orgaStats1']));

        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['nickname' => 'orgaStats2']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('GET', '/api/organization/orgastats/stats/citizens', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            'citizenMostShips' => [
                'citizen' => [
                    'id' => '79245ea3-73c8-4d34-83e6-2adff81f417d',
                    'handle' => 'Anonymous', // orgaStats1 with publicChoice=private
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
        $this->client->xmlHttpRequest('GET', '/api/organization/flk/stats/citizens', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_enough_rights_public', $json['error']);
    }
}
