<?php

namespace App\Tests\Controller\Profile;

use App\Domain\CitizenNumber;
use App\Domain\HandleSC;
use App\Entity\Citizen;
use App\Entity\User;
use App\Service\Citizen\InfosProvider\CitizenInfosProviderInterface;
use App\Tests\WebTestCase;

class LinkAccountControllerTest extends WebTestCase
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
