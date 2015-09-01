<?php

use Illuminate\Database\Schema\Blueprint;

class FastMigrationRunner {

    protected $bufferedAttributeMigrations = [];
    protected $bufferedRelationMigrations = [];
    protected $tables = [];

    public function run($tables)
    {
        $this->tables = $tables;
        $this->bufferMigrations();
        $this->runBufferedAttributeMigrations();
        $this->runBufferedRelationMigrations();
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
        return starts_with($key, 'toHave') || starts_with($key, 'toMorphMany');
    }

    private function runBufferedAttributeMigrations()
    {
        foreach ($this->bufferedAttributeMigrations as $table_name => $migrations) {
            Schema::create($table_name, function (Blueprint $table) use ($migrations) {
                $table->increments('id');
                $table->timestamps();

                foreach ($migrations as $key => $columns) {
                    $type = strtolower(str_singular(str_replace('with', '', $key)));
                    foreach ($columns as $column_name) {
                        $table->$type($column_name);
                    }
                }
            });
        }
    }

    private function runBufferedRelationMigrations()
    {
        foreach ($this->bufferedRelationMigrations as $table_name => $migrations) {
            $many_relations = array_flatten(array_where($migrations, function($key, $val) {
                return ends_with($key, 'Many');
            }));
            foreach ($many_relations as $relation) {
                Schema::table($relation, function (Blueprint $table) use ($table_name, $relation) {
                    $table->integer(str_singular($table_name).'_id');
                });
            }

            $one_relations = array_flatten(array_where($migrations, function($key, $val) {
                return ends_with($key, 'One');
            }));
            Schema::table($table_name, function (Blueprint $table) use ($one_relations) {
                foreach ($one_relations as $relation) {
                    $table->integer(str_singular($relation).'_id');
                }
            });
        }
    }

}