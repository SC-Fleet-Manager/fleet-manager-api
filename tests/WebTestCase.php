<?php

namespace App\Tests;

use App\Entity\User;
use App\Tests\Constraint\ArraySubset;
use Doctrine\Persistence\ManagerRegistry;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;
use HWI\Bundle\OAuthBundle\Security\Core\Authentication\Token\OAuthToken;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\TestBrowserToken;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\InvalidArgumentException;
use Symfony\Component\Security\Guard\Token\PostAuthenticationGuardToken;

class WebTestCase extends BaseWebTestCase
{
    use ReloadDatabaseTrait;

    protected KernelBrowser $client;
    protected ManagerRegistry $doctrine;

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
        $this->doctrine = static::$container->get(ManagerRegistry::class);
        $this->doctrine->getManager()->clear();
    }

    protected static function generateToken(string $username): string
    {
        return (new Builder())
            ->withClaim("iss", "https://test_domain/")
            ->withClaim("sub", $username)
            ->withClaim("aud", ["test_audience"])
            ->withClaim("exp", 1616009284)
            ->withClaim("azp", "test_clientid")
            ->getToken(new Sha256(), new Key('secret'))
        ;
    }

    protected function debugHtml(): void
    {
        file_put_contents(__DIR__.'/../var/debug.html', $this->client->getResponse()->getContent());
    }

    /**
     * Asserts that an array has a specified subset.
     *
     * @param array|\ArrayAccess|mixed[] $subset
     * @param array|\ArrayAccess|mixed[] $array
     *
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    public static function assertArraySubset($subset, $array, bool $checkForObjectIdentity = false, string $message = ''): void
    {
        if (! (is_array($subset) || $subset instanceof \ArrayAccess)) {
            throw InvalidArgumentException::create(
                1,
                'array or ArrayAccess'
            );
        }
        if (! (is_array($array) || $array instanceof \ArrayAccess)) {
            throw InvalidArgumentException::create(
                2,
                'array or ArrayAccess'
            );
        }
        $constraint = new ArraySubset($subset, $checkForObjectIdentity);
        static::assertThat($array, $constraint, $message);
    }
}
