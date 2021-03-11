<?php

namespace App\Tests\Controller\Profile;

use App\Entity\User;
use App\Tests\WebTestCase;

class LadderAlltimeControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group funding
     */
    public function testIndex(): void
    {
        $this->logIn($this->doctrine->getRepository(User::class)->findOneBy(['nickname' => '10_fundings']));

        $this->client->xmlHttpRequest('GET', '/api/funding/ladder-alltime', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        static::assertArraySubset([
            'topFundings' => [
                [
                    'rank' => 1,
                    'amount' => 5133,
                    'name' => 'ionni',
                    'me' => false,
                ],
                [
                    'rank' => 2,
                    'amount' => 2150,
                    'name' => 'Anonymous', // user_fundings_1 /w supporterVisible == false
                    'me' => false,
                ],
                [
                    'rank' => 3,
                    'amount' => 1555,
                    'name' => 'fundings-2',
                    'me' => false,
                ],
                [
                    'rank' => 3,
                    'amount' => 1555,
                    'name' => 'fundings-3',
                    'me' => false,
                ],
                [
                    'rank' => 5,
                    'amount' => 700,
                    'name' => 'ashuvidz',
                    'me' => false,
                ],
                [
                    'rank' => 6,
                    'amount' => 130,
                    'name' => '30_fundings',
                    'me' => false,
                ],
                [
                    'rank' => 7,
                    'amount' => 129,
                    'name' => '29_fundings',
                    'me' => false,
                ],
                [
                    'rank' => 8,
                    'amount' => 128,
                    'name' => '28_fundings',
                    'me' => false,
                ],
                [
                    'rank' => 9,
                    'amount' => 127,
                    'name' => '27_fundings',
                    'me' => false,
                ],
                [
                    'rank' => 10,
                    'amount' => 126,
                    'name' => '26_fundings',
                    'me' => false,
                ],
                [
                    'rank' => 11,
                    'amount' => 125,
                    'name' => '25_fundings',
                    'me' => false,
                ],
                [
                    'rank' => 12,
                    'amount' => 124,
                    'name' => '24_fundings',
                    'me' => false,
                ],
                [
                    'rank' => 13,
                    'amount' => 123,
                    'name' => '23_fundings',
                    'me' => false,
                ],
                [
                    'rank' => 14,
                    'amount' => 122,
                    'name' => '22_fundings',
                    'me' => false,
                ],
                [
                    'rank' => 15,
                    'amount' => 121,
                    'name' => '21_fundings',
                    'me' => false,
                ],
                [
                    'rank' => 16,
                    'amount' => 120,
                    'name' => '20_fundings',
                    'me' => false,
                ],
                [
                    'rank' => 17,
                    'amount' => 119,
                    'name' => '19_fundings',
                    'me' => false,
                ],
                [
                    'rank' => 18,
                    'amount' => 118,
                    'name' => '18_fundings',
                    'me' => false,
                ],
                [
                    'rank' => 19,
                    'amount' => 117,
                    'name' => '17_fundings',
                    'me' => false,
                ],
                [
                    'rank' => 20,
                    'amount' => 116,
                    'name' => '16_fundings',
                    'me' => false,
                ],
                [
                    'rank' => 26,
                    'amount' => 110,
                    'name' => '10_fundings',
                    'me' => true,
                ],
            ],
        ], $json);
    }

    /**
     * @group functional
     * @group funding
     */
    public function testOrga(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/funding/ladder-alltime', [
            'orgaMode' => true,
        ], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        static::assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        static::assertArraySubset([
            'topFundings' => [
                [
                    'rank' => 1,
                    'amount' => 5833,
                    'name' => 'Anonymous', // FallKrom /w supporterVisible==false
                    'me' => false,
                ],
                [
                    'rank' => 2,
                    'amount' => 90,
                    'name' => 'Les Gardiens',
                    'me' => false,
                ],
            ],
        ], $json);
    }
}
