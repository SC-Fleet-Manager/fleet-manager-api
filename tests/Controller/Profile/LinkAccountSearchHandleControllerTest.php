<?php

namespace App\Tests\Controller\MyFleet;

use App\Domain\CitizenNumber;
use App\Domain\HandleSC;
use App\Entity\Citizen;
use App\Entity\CitizenOrganization;
use App\Service\Citizen\InfosProvider\CitizenInfosProviderInterface;
use App\Tests\WebTestCase;

class LinkAccountSearchHandleControllerTest extends WebTestCase
{
    /**
     * @group functional
     * @group profile
     */
    public function testSearchHandle(): void
    {
        $citizen = new Citizen();
        $citizen->setActualHandle(new HandleSC('foobar'));
        $citizen->setNickname('Foo bar');
        $citizen->setNumber(new CitizenNumber('123456'));
        $citizen->setMainOrga((new CitizenOrganization())->setOrganizationSid('myorga')->setRank(1)->setRankName('Newbie'));

        $citizenInfosProvider = static::$container->get(CitizenInfosProviderInterface::class);
        $citizenInfosProvider->setCitizen($citizen);

        $this->client->xmlHttpRequest('GET', '/api/profile/search-handle', [
            'handle' => 'foobar',
        ], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertArraySubset([
            'numberSC' => ['number' => '123456'],
            'nickname' => 'Foo bar',
            'handle' => ['handle' => 'foobar'],
            'mainOrga' => ['sid' => ['sid' => 'myorga'], 'rank' => 1, 'rankName' => 'Newbie'],
            'avatarUrl' => 'http://example.com/fake-avatar.png',
            'bio' => null,
        ], $json);
    }

    /**
     * @group functional
     * @group profile
     */
    public function testSearchHandleNotFound(): void
    {
        $citizenInfosProvider = static::$container->get(CitizenInfosProviderInterface::class);
        $citizenInfosProvider->setCitizen(null);

        $this->client->xmlHttpRequest('GET', '/api/profile/search-handle', [
            'handle' => 'not_exist',
        ], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('not_found_handle', $json['error']);
    }

    /**
     * @group functional
     * @group profile
     */
    public function testSearchHandleNoParam(): void
    {
        $this->client->xmlHttpRequest('GET', '/api/profile/search-handle', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $this->assertSame(400, $this->client->getResponse()->getStatusCode());
        $json = \json_decode($this->client->getResponse()->getContent(), true);
        $this->assertSame('invalid_form', $json['error']);
    }
}
