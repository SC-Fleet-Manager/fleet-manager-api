<?php

namespace App\Tests\Controller;

use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use Symfony\Component\Panther\PantherTestCase;

class DashboardControllerTest extends PantherTestCase
{
    use ReloadDatabaseTrait;

    private $client;

    public function setUp()
    {
        $this->client = static::createPantherClient('127.0.0.1', 9100);
    }

    public function testIndexSuccessResponse(): void
    {
        $crawler = $this->client->request('GET', '/login');

//        $this->assertSame('Ensemble de la flotte', $crawler->filter('.card-header')->text());
    }
}
