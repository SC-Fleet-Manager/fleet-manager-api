<?php

namespace App\Tests\End2End;

use App\Tests\Constraint\ArraySubset;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Signer\Key;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

class WebTestCase extends BaseWebTestCase
{
    protected static KernelBrowser $client;
    protected static ManagerRegistry $doctrine;
    protected static Connection $connection;

    public function setUp(): void
    {
        parent::setUp();
        static::$client = static::createClient();
        static::$doctrine = static::$container->get(ManagerRegistry::class);
        static::$doctrine->getManager()->clear();
        static::$connection = static::$doctrine->getConnection();
        static::$connection->beginTransaction();
    }

    protected function tearDown(): void
    {
        if (static::$connection->isTransactionActive()) {
            static::$connection->rollBack();
        }
        parent::tearDown();
    }

    protected static function generateToken(string $username, ?string $nickname = null, ?string $email = null): string
    {
        return (new Builder())
            ->withClaim('iss', 'https://test_domain/')
            ->withClaim('https://api.fleet-manager.space/nickname', $nickname)
            ->withClaim('https://api.fleet-manager.space/email', $email)
            ->withClaim('sub', $username)
            ->withClaim('aud', ['test_audience'])
            ->withClaim('exp', time())
            ->withClaim('azp', 'test_clientid')
            ->getToken(new Sha256(), new Key('secret'));
    }

    protected function debugHtml(): void
    {
        file_put_contents(__DIR__.'/../var/debug.html', static::$client->getResponse()->getContent());
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
        if (!(is_array($subset) || $subset instanceof \ArrayAccess)) {
            throw InvalidArgumentException::create(1, 'array or ArrayAccess');
        }
        if (!(is_array($array) || $array instanceof \ArrayAccess)) {
            throw InvalidArgumentException::create(2, 'array or ArrayAccess');
        }
        $constraint = new ArraySubset($subset, $checkForObjectIdentity);
        static::assertThat($array, $constraint, $message);
    }
}
