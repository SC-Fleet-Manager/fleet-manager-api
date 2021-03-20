<?php

namespace App\Tests\Acceptance;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as BaseKernelTestCase;

class KernelTestCase extends BaseKernelTestCase
{
    protected static Connection $connection;

    protected function setUp(): void
    {
        static::bootKernel(['environment' => 'test_acceptance']);
    }
}
