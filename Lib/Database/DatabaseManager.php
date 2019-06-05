<?php

class Database_DatabaseManager
{
    protected $factory;

    public function __construct($factory)
    {
        $this->factory = $factory;
    }

    public function connection($name = null)
    {
        list($database, $type) = $this->parseConnectionName($name);

        $name = $name ?: $database;

        if (!isset($this->connections[$name])) {
            $this->connections[$name] = $this->makeConnection($database);
            /*
            $this->connections[$name] = $this->configure(
                $this->makeConnection($database), $type
            );
            */
        }

        return $this->connections[$name];
    }

    protected function parseConnectionName($name)
    {
        $name = $name ?: $this->getDefaultConnection();

        foreach (['::read', '::write'] as $needle) {
            if (substr($name, -strlen($needle)) === (string)$needle) {
                return explode('::', $name, 2);
            }
        }

        return [$name, null];
    }

    protected function makeConnection($name)
    {
        $config = $this->configuration($name);

        return $this->factory->make($config, $name);
    }

    protected function configuration($name)
    {
        $name = $name ?: $this->getDefaultConnection();

        $connections = Lib_Config::get("resource.mysql");

        if (!array_key_exists($name, $connections)) {
            throw new InvalidArgumentException("Database [{$name}] not configured.");
        }

        return $connections[$name];
    }

    public function getDefaultConnection()
    {
        return Lib_Config::get('resource.default');
    }

    public function __call($method, $parameters)
    {
        return $this->connection()->$method(...$parameters);
    }
}