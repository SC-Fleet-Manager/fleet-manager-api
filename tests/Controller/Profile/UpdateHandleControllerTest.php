<?php

namespace App\Tests\Controller\MyFleet;

use App\Domain\CitizenNumber;
use App\Domain\HandleSC;
use App\Entity\Citizen;
use App\Entity\User;
use App\Service\Citizen\InfosProvider\CitizenInfosProviderInterface;
use App\Tests\WebTestCase;

class UpdateHandleControllerTest extends WebTestCase
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
}
