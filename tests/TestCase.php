<?php

namespace Tests;

use FunkyDuck\Querychan\ORM\Database;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void 
    {
        parent::setUp();

        $config = [
            'driver' => 'sqlite',
            'database' => ':memory:'
        ];

        Database::connect($config);
    }

    protected function tearDown(): void
    {
        Database::disconnect();
        parent::tearDown();
    }
}