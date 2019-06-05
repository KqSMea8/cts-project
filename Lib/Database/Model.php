<?php

class Database_Model
{
    protected $connection;

    protected $table;

    public function getConnectionName()
    {
        return $this->connection;
    }

    public function setConnection($name)
    {
        $this->connection = $name;

        return $this;
    }

    public function getTable()
    {
        return $this->table;
    }

    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    public function getConnection()
    {
        return DB::connection($this->getConnectionName());
    }

    public function newQuery()
    {
        $connection = $this->getConnection();

        $query = new Database_Query_Builder(
            $connection, $connection->getQueryGrammar(), $connection->getPostProcessor()
        );

        $query->from($this->getTable());

        return $query;
    }

    public function __call($method, $parameters)
    {
        return $this->newQuery()->$method(...$parameters);
    }

    public static function __callStatic($method, $parameters)
    {
        return (new static)->$method(...$parameters);
    }
}