<?php

namespace FastMigrate;

use Illuminate\Database\Migrations\Migration;

abstract class FastMigrator extends Migration
{
    protected $tables = [];
    protected $curr_table_name = '';

    public function amReadyForMigration()
    {
        $migration_runner = new FastMigrationRunner;
        $migration_runner->run($this->tables);
    }

    public function wantATable($table_name)
    {
        $this->tables[$table_name] = [];
        return $this->want($table_name);
    }

    public function want($table_name)
    {
        $this->curr_table_name = $table_name;
        return $this;
    }

    public function __call($name, $arguments)
    {
        $this->setTableProperty($name, $arguments);
        return $this;
    }
    
    private function setTableProperty($key, $val)
    {
        $this->tables[$this->curr_table_name][$key] = $val;
    }
    
}
