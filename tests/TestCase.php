<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUpTraits()
    {
        $this->configureEnvironment();

        return parent::setUpTraits();
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->configureEnvironment();
    }

    private function configureEnvironment(): void
    {
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => ':memory:',
            'mail.default' => 'log',
        ]);

        config([
            'queue.default' => 'sync',
            'scout.driver' => null,
        ]);
    }
}
