<?php

namespace App\Tests\Integration;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as BaseKernelTestCase;

class KernelTestCase extends BaseKernelTestCase
{
    protected static Connection $connection;

    protected function setUp(): void
    {
        static::bootKernel();
        static::$connection = static::$container->get('doctrine.dbal.default_connection');
        static::$connection->beginTransaction();
    }

    protected function tearDown(): void
    {
        if (static::$connection->isTransactionActive()) {
            static::$connection->rollBack();
        }
    }
}
