<?php

namespace App\Tests\Controller;

use App\Domain\CitizenNumber;
use App\Domain\HandleSC;
use App\Entity\Citizen;
use App\Entity\User;
use App\Service\CitizenInfosProviderInterface;
use App\Tests\WebTestCase;

class ProfileControllerTest extends WebTestCase
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
     * @group profile
     */
    public function testIndex(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('GET', '/api/profile', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            'id' => 'd92e229e-e743-4583-905a-e02c57eacfe0',
            'username' => '123456789001',
            'token' => '4682bc58961264de31d38bf6af18cfe717ab2ba59f34b906668b4d7c0ca65b33',
            'citizen' => [
                'id' => '7275c744-6a69-43c2-9ebf-1491a104d5e7',
                'number' => [
                    'number' => '123456',
                ],
                'actualHandle' => [
                    'handle' => 'ionni',
                ],
                'mainOrga' => [
                    'id' => '41ade55e-6d32-419c-9e48-169fd6c61f34',
                    'organization' => [
                        'organizationSid' => 'flk',
                        'name' => 'FallKrom',
                        'avatarUrl' => null,
                    ],
                    'rank' => 1,
                    'rankName' => 'Citoyen',
                ],
                'organizations' => [
                    [
                        'id' => '41ade55e-6d32-419c-9e48-169fd6c61f34',
                        'organization' => [
                            'organizationSid' => 'flk',
                            'name' => 'FallKrom',
                            'avatarUrl' => null,
                        ],
                        'rank' => 1,
                        'rankName' => 'Citoyen',
                    ],
                ],
                'bio' => 'This is my bio',
                'countRedactedOrganizations' => 0,
                'redactedMainOrga' => false,
            ],
            'publicChoice' => 'public',
            'createdAt' => '2019-04-02T11:22:33+00:00',
            'nickname' => 'Ioni',
        ], $json);
    }

    /**
     * @group functional
     * @group profile
     */
    public function testIndexNotAuth(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/profile', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_auth', $json['error']);
    }

    /**
     * @group functional
     * @group profile
     */
    public function testRefreshRsiProfile(): void
    {
        /** @var User $user */
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'needRefresh']);
        $this->logIn($user);

        $this->assertNull($user->getCitizen()->getMainOrga(), 'Main orga of user need_refresh must be null before refresh.');
        $this->assertCount(0, $user->getCitizen()->getOrganizations(), 'User need_refresh must have no orgas before refresh.');

        $this->client->xmlHttpRequest('POST', '/api/profile/refresh-rsi-profile', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(204, $this->client->getResponse()->getStatusCode());

        $this->assertSame('80db0703-dd43-49a0-93d3-89947b9ab321', $user->getCitizen()->getMainOrga()->getOrganization()->getId()->toString());
        $this->assertSame('flk', $user->getCitizen()->getMainOrga()->getOrganization()->getOrganizationSid());

        $this->assertCount(2, $user->getCitizen()->getOrganizations());
        $orga1 = $user->getCitizen()->getOrganizations()[0];
        $this->assertSame('80db0703-dd43-49a0-93d3-89947b9ab321', $orga1->getOrganization()->getId()->toString());
        $this->assertSame('flk', $orga1->getOrganization()->getOrganizationSid());
        $this->assertSame('FallKrom', $orga1->getOrganization()->getName());
        $this->assertSame(1, $orga1->getRank());
        $this->assertSame('Newbie', $orga1->getRankName());

        $orga2 = $user->getCitizen()->getOrganizations()[1];
        $this->assertSame('901ccbf8-fa63-4b07-81aa-f10f60954715', $orga2->getOrganization()->getId()->toString());
        $this->assertSame('gardiens', $orga2->getOrganization()->getOrganizationSid());
        $this->assertSame('Les Gardiens', $orga2->getOrganization()->getName());
        $this->assertSame(1, $orga2->getRank());
        $this->assertSame('Noob', $orga2->getRankName());
    }

    /**
     * @group functional
     * @group profile
     */
    public function testRefreshRsiProfileTooManyRefresh(): void
    {
        $this->logIn($this->user);
        $this->client->insulate();
        $this->client->xmlHttpRequest('POST', '/api/profile/refresh-rsi-profile', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->client->insulate();
        $this->client->xmlHttpRequest('POST', '/api/profile/refresh-rsi-profile', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('too_many_refresh', $json['error']);
    }

    /**
     * @group functional
     * @group profile
     */
    public function testRefreshRsiProfileNoCitizen(): void
    {
        $user = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'NoCitizen']);
        $this->logIn($user);
        $this->client->xmlHttpRequest('POST', '/api/profile/refresh-rsi-profile', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_citizen', $json['error']);
    }

    /**
     * @group functional
     * @group profile
     */
    public function testRefreshRsiProfileNotAuth(): void
    {
        $this->client->xmlHttpRequest('POST', '/api/profile/refresh-rsi-profile', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('no_auth', $json['error']);
    }

    /**
     * @group functional
     * @group profile
     */
    public function testSavePreferences(): void
    {
        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/profile/save-preferences', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'publicChoice' => 'private',
        ]));
        $this->assertSame(204, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group profile
     */
    public function testSavePreferencesNotAuth(): void
    {
        $this->client->xmlHttpRequest('POST', '/api/profile/save-preferences', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group profile
     */
    public function testUpdateHandle(): void
    {
        $citizen = new Citizen();
        $citizen->setActualHandle(new HandleSC('foobar'));
        $citizen->setNickname('Foo bar');
        $citizen->setNumber(clone $this->user->getCitizen()->getNumber()); // same number !

        $citizenInfosProvider = static::$container->get(CitizenInfosProviderInterface::class);
        $citizenInfosProvider->setCitizen($citizen);

        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/profile/update-handle', [
            'handleSC' => 'foobar',
        ], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertSame(204, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group profile
     */
    public function testUpdateHandleBadNumber(): void
    {
        $citizen = new Citizen();
        $citizen->setActualHandle(new HandleSC('foobar'));
        $citizen->setNickname('Foo bar');
        $citizen->setNumber(new CitizenNumber('foobarbaz')); // different number !

        $citizenInfosProvider = static::$container->get(CitizenInfosProviderInterface::class);
        $citizenInfosProvider->setCitizen($citizen);

        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/profile/update-handle', [
            'handleSC' => 'foobar',
        ], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('invalid_form', $json['error']);
    }

    /**
     * @group functional
     * @group profile
     */
    public function testUpdateHandleNotAuth(): void
    {
        $this->client->xmlHttpRequest('POST', '/api/profile/update-handle', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group functional
     * @group profile
     */
    public function testLinkAccount(): void
    {
        $citizen = new Citizen();
        $citizen->setActualHandle(new HandleSC('foobar'));
        $citizen->setNickname('Foo bar');
        $citizen->setNumber(new CitizenNumber('123456789'));
        $citizen->setBio('4682bc58961264de31d38bf6af18cfe717ab2ba59f34b906668b4d7c0ca65b33'); // same as $this->user->bio !

        $citizenInfosProvider = static::$container->get(CitizenInfosProviderInterface::class);
        $citizenInfosProvider->setCitizen($citizen);

        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/profile/link-account', [
            'handleSC' => 'foobar',
        ], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertSame(204, $this->client->getResponse()->getStatusCode());

        $this->assertSame('123456789', $this->user->getCitizen()->getNumber()->getNumber());
        $this->assertSame('foobar', $this->user->getCitizen()->getActualHandle()->getHandle());
        $this->assertSame('4682bc58961264de31d38bf6af18cfe717ab2ba59f34b906668b4d7c0ca65b33', $this->user->getCitizen()->getBio());
    }

    /**
     * @group functional
     * @group profile
     */
    public function testLinkAccountAlreadyLinked(): void
    {
        $citizen = new Citizen();
        $citizen->setActualHandle(new HandleSC('ashuvidz')); // handle already linked
        $citizen->setNickname('Vyrtual Synthese');
        $citizen->setNumber(new CitizenNumber('123456789'));
        $citizen->setBio('4682bc58961264de31d38bf6af18cfe717ab2ba59f34b906668b4d7c0ca65b33'); // same as $this->user->bio !

        $citizenInfosProvider = static::$container->get(CitizenInfosProviderInterface::class);
        $citizenInfosProvider->setCitizen($citizen);

        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/profile/link-account', [
            'handleSC' => 'foobar',
        ], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertSame(204, $this->client->getResponse()->getStatusCode());

        $this->assertSame('123456789', $this->user->getCitizen()->getNumber()->getNumber());
        $this->assertSame('ashuvidz', $this->user->getCitizen()->getActualHandle()->getHandle());

        $oldUser = $this->doctrine->getRepository(User::class)->findOneBy(['username' => 'Ashuvidz']);
        $this->assertNull($oldUser->getCitizen(), 'Citizen of old user should be null.');
    }

    /**
     * @group functional
     * @group profile
     */
    public function testLinkAccountBadToken(): void
    {
        $citizen = new Citizen();
        $citizen->setActualHandle(new HandleSC('foobar'));
        $citizen->setNickname('Foo bar');
        $citizen->setNumber(new CitizenNumber('123456789'));
        $citizen->setBio('foobar'); // different as $this->user->bio !

        $citizenInfosProvider = static::$container->get(CitizenInfosProviderInterface::class);
        $citizenInfosProvider->setCitizen($citizen);

        $this->logIn($this->user);
        $this->client->xmlHttpRequest('POST', '/api/profile/link-account', [
            'handleSC' => 'foobar',
        ], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('invalid_form', $json['error']);
    }

    /**
     * @group functional
     * @group profile
     */
    public function testLinkAccountNotAuth(): void
    {
        $this->client->xmlHttpRequest('POST', '/api/profile/link-account', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);
        $this->assertSame(401, $this->client->getResponse()->getStatusCode());
    }
}
