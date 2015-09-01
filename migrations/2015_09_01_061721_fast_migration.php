<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FastMigration extends Migration
{

    use FastMigrator;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->wantATable('users')
            ->withStrings('username', 'password');

        $this->wantATable('roles')
            ->withStrings('description');

        $this->wantATable('posts')
            ->withStrings('title', 'content')
            ->withInteger('score');

        $this->want('users')
            ->toHaveMany('posts');
        $this->want('users')
            ->toHaveOne('roles');

        $this->amReadyForMigration();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('roles');
    }
}
