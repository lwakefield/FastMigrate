<?php

use Illuminate\Support\Facades\Facade;
use FastMigrate\FastMigrator;

class FastMigrateTest extends Illuminate\Foundation\Testing\TestCase
{

    public function createApplication()
    {
        $app = require __DIR__.'/../../vendor/laravel/laravel/bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }

    public function setUp()
    {
        parent::setUp();

        $this->app['config']->set('database.default', 'testing');

        $this->app['config']->set('database.connections.testing', array(
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ));
    }

    public function testCanCreateTable()
    {
        $I = $this->getMockForAbstractClass('FastMigrate\FastMigrator');
        $I->wantATable('flights');
        $I->amReadyForMigration();

        $this->assertTrue(Schema::hasTable('flights'));
    }
}
