<?php

namespace App\Tests;

use App\Entity\User;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\BrowserKit\Cookie;

class WebTestCase extends BaseWebTestCase
{
    use RefreshDatabaseTrait;

    /** @var Client */
    protected $client;
    /** @var RegistryInterface */
    protected $doctrine;

    protected static function createClient(array $options = [], array $server = [])
    {
        static::bootKernel($options);

        $client = static::$kernel->getContainer()->get('test.client');
        $client->setServerParameters($server);

        return $client;
    }

    public function setUp(): void
    {
        $this->client = static::createClient();
        $this->doctrine = static::$container->get('doctrine');
    }

    protected function logIn(User $user): void
    {
        $session = $this->client->getContainer()->get('session');

        $token = new OAuthToken(md5(mt_rand()), $user->getRoles());
        $token->setResourceOwnerName('discord');
        $token->setUser($user);
        $token->setAuthenticated(true);
        $session->set('_security_main', serialize($token));
        $session->save();

        $this->client->getCookieJar()->set(new Cookie($session->getName(), $session->getId()));
    }
}
