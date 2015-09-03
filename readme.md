# FastMigrate

Migrate even faster with FastMigrate! FastMigrate is essentially a wrapper around Laravels migration class. FastMigrate aims to make creating tables easier and faster.

FastMigrate requires Laravel >= 5.0.

## Installation

To install, simply `composer require lawrence/fast-migrate:dev-master`

## Example

An example of FastMigrate in use is shown below.

```php
<?php

use FastMigrate\FastMigrator;
use Schema;

class ExampleMigration extends FastMigrate
{

    public function up()
    {
        $I = $this;
        $I->wantATable('users')
            ->withStrings('username', 'password');

        $I->wantATable('roles')
            ->withStrings('description');

        $I->wantATable('posts')
            ->withStrings('title', 'content')
            ->withInteger('score');

        $I->want('users')
            ->toHaveMany('posts');
        $I->want('users')
            ->toHaveOne('roles');

        $I->amReadyForMigration();
    }

    public function down()
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('posts');
        Schema::dropIfExists('roles');
    }
}
```

Running the above migration will generate three tables as follows:

```
- users:
    - id
    - created_at
    - updated_at
    - username
    - password
    - role_id

- roles:
    - id
    - created_at
    - updated_at
    - description

- posts:
    - id
    - created_at
    - updated_at
    - title
    - content
    - score
    - user_id
```
