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
        $I->wantATable('users')->
            amReadyForMigration();

        $this->assertTrue(Schema::hasTable('users'));
    }

    public function testCanCreateColumn()
    {
        $I = $this->getMockForAbstractClass('FastMigrate\FastMigrator');
        $I->wantATable('users')
            ->withStrings('username', 'password')
            ->amReadyForMigration();

        $this->assertTrue(Schema::hasColumns('users', ['username', 'password']));
    }

    public function testCanCreateRelationToOne()
    {
        $I = $this->getMockForAbstractClass('FastMigrate\FastMigrator');
        $I->wantATable('users')
            ->toHaveOne('profiles');
        $I->wantATable('profiles');
        $I->amReadyForMigration();

        $this->assertTrue(Schema::hasColumns('profiles', ['user_id']));
    }
    
    
    public function testCanCreateRelationToMany()
    {
        $I = $this->getMockForAbstractClass('FastMigrate\FastMigrator');
        $I->wantATable('users')
            ->toHaveMany('posts');
        $I->wantATable('posts');
        $I->amReadyForMigration();

        $this->assertTrue(Schema::hasColumns('posts', ['user_id']));
    }

    public function testCanCreateManyToMany()
    {
        $I = $this->getMockForAbstractClass('FastMigrate\FastMigrator');
        $I->wantATable('posts')
            ->manyToMany('tags');
        $I->wantATable('tags');
        $I->amReadyForMigration();

        $this->assertTrue(Schema::hasColumns('post_tag', ['post_id', 'tag_id']));
    }
    
}
