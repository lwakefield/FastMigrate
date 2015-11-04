<?php

namespace FastMigrate;

use \Illuminate\Database\Schema\Blueprint;
use Schema;

class FastMigrationRunner {

    protected $bufferedAttributeMigrations = [];
    protected $bufferedRelationMigrations = [];
    protected $tables = [];

    public function run($tables)
    {
        $this->tables = $tables;
        $this->makeTables();
        $this->bufferMigrations();
        $this->runBufferedAttributeMigrations();
        $this->runBufferedRelationMigrations();
    }

    private function makeTables()
    {
        foreach ($this->tables as $table_name => $val) {
            Schema::create($table_name, function (Blueprint $table) {
                $table->increments('id');
                $table->timestamps();
            });
        }
    }

    public function bufferMigrations()
    {
        foreach ($this->tables as $table => $migrations) {
            $this->bufferedAttributeMigrations[$table] = array_where($migrations, function($key, $val) {
                return $this->isAttribute($key);
            });
            $this->bufferedRelationMigrations[$table] = array_where($migrations, function($key, $val) {
                return $this->isRelation($key);
            });
        }
    }

    private function isAttribute($key)
    {
        return starts_with($key, 'with');
    }

    private function isRelation($key)
    {
        return starts_with($key, ['belongsTo', 'toHave', 'morphsTo', 'manyToMany']);
    }

    private function runBufferedAttributeMigrations()
    {
        foreach ($this->bufferedAttributeMigrations as $table_name => $migrations) {
            Schema::table($table_name, function (Blueprint $table) use ($migrations) {
                foreach ($migrations as $key => $columns) {
                    $type = strtolower(str_singular(str_replace('with', '', $key)));
                    foreach ($columns as $column_name) {
                        $table->$type($column_name)->nullable();
                    }
                }
            });
        }
    }

    private function runBufferedRelationMigrations()
    {
        foreach ($this->bufferedRelationMigrations as $table_name => $migrations) {
            $this->runManyToManyMigrations($table_name, $migrations);
            $this->runToManyMigrations($table_name, $migrations);
            $this->runToOneMigrations($table_name, $migrations);
            $this->runBelongsToMigrations($table_name, $migrations);
        }
    }

    private function runManyToManyMigrations($table_name, $migrations)
    {
        $relations = $this->getRelations($migrations, 'manyToMany');
        foreach ($relations as $relation) {
            $name_a = str_singular($table_name);
            $name_b = str_singular($relation);
            $relation_table_name = $name_a.'_'.$name_b;
            Schema::create($relation_table_name, function (Blueprint $table) use ($name_a, $name_b) {
                $table->integer($name_a.'_id');
                $table->integer($name_b.'_id');
            });
        }
    }

    private function runToManyMigrations($table_name, $migrations)
    {
        $relations = $this->getRelations($migrations, 'toHaveMany');
        foreach ($relations as $relation) {
            Schema::table($relation, function (Blueprint $table) use ($table_name, $relation) {
                $table->
                    integer(str_singular($table_name).'_id')->
                    default(-1);
            });
        }
    }
    
    private function runToOneMigrations($table_name, $migrations)
    {
        $relations = $this->getRelations($migrations, 'toHaveOne');
        foreach ($relations as $relation) {
            Schema::table($relation, function (Blueprint $table) use ($table_name, $relation) {
                $table->
                    integer(str_singular($table_name).'_id')->
                    default(-1);
            });
        }
    }

     private function runBelongsToMigrations($table_name, $migrations)
     {
         $relations = $this->getRelations($migrations, 'belongsTo');
         Schema::table($table_name, function (Blueprint $table) use ($relations) {
             foreach ($relations as $relation) {
                 $table->
                     integer(str_singular($relation).'_id')->
                     default(-1);
             }
         });
     }

    private function getRelations($migrations, $type)
    {
        return array_flatten(array_where($migrations, function($key, $val) use ($type) {
            return ends_with($key, $type);
        }));
    }
    
    
}
