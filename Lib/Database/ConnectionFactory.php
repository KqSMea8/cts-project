<?php

class Database_ConnectionFactory
{
    public function make(array $config, $name = null)
    {
        $config = $this->parseConfig($config, $name);

        if (isset($config['read'])) {
            return $this->createReadWriteConnection($config);
        }

        return $this->createSingleConnection($config);
    }

    protected function parseConfig(array $config, $name)
    {
        $config['prefix'] = '';
        $config['name'] = $name;

        return $config;
    }

    protected function createSingleConnection(array $config)
    {
        $pdo = $this->createPdoResolver($config);

        return $this->createConnection(
            $pdo, $config['name'], $config['prefix'], $config
        );
    }

    protected function createReadWriteConnection(array $config)
    {
        $connection = $this->createSingleConnection($this->getWriteConfig($config));

        return $connection->setReadPdo($this->createReadPdo($config));
    }

    protected function getWriteConfig(array $config)
    {
        return $this->mergeReadWriteConfig(
            $config, $this->getReadWriteConfig($config, 'write')
        );
    }

    protected function getReadWriteConfig(array $config, $type)
    {
        return isset($config[$type][0])
            ? $config[$type][array_rand($config[$type])]
            : $config[$type];
    }

    protected function mergeReadWriteConfig(array $config, array $merge)
    {
        $config = array_merge($config, $merge);

        foreach (['read', 'write'] as $key) {
            if (array_key_exists($key, $config)) {
                unset($config[$key]);
                continue;
            }
        }

        return $config;
    }

    protected function createPdoResolver(array $config)
    {
        return $this->createPdoResolverWithHosts($config);
    }

    protected function createPdoResolverWithHosts(array $config)
    {
        return function () use ($config) {
            return $this->createConnector($config)->connect($config);
        };
    }

    protected function createReadPdo(array $config)
    {
        return $this->createPdoResolver($this->getReadConfig($config));
    }

    protected function getReadConfig(array $config)
    {
        return $this->mergeReadWriteConfig(
            $config, $this->getReadWriteConfig($config, 'read')
        );
    }

    public function createConnector(array $config)
    {
        return new Database_MySqlConnector;
    }

    protected function createConnection($connection, $database, $prefix = '', array $config = [])
    {
        return new Database_MySqlConnection($connection, $database, $prefix, $config);
    }
}