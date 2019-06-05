<?php

class Database_MysqlConnector extends Database_Connector
{
    public function connect(array $config)
    {
        $dsn = $this->getDsn($config);

        $options = $this->getOptions($config);

        $connection = $this->createConnection($dsn, $config, $options);

        if (!empty($config['name'])) {
            $connection->exec("use `{$config['name']}`;");
        }

        $this->configureEncoding($connection, $config);

        $this->configureTimezone($connection, $config);

        $this->setModes($connection, $config);

        return $connection;
    }

    protected function configureEncoding($connection, array $config)
    {
        if (! isset($config['charset'])) {
            $config['charset'] = 'utf8';//默认uf8
        }

        $connection->prepare(
            "set names '{$config['charset']}'".$this->getCollation($config)
        )->execute();
    }

    protected function getCollation(array $config)
    {
        return isset($config['collation']) ? " collate '{$config['collation']}'" : '';
    }

    protected function configureTimezone($connection, array $config)
    {
        if (isset($config['timezone'])) {
            $connection->prepare('set time_zone="'.$config['timezone'].'"')->execute();
        }
    }

    protected function getDsn(array $config)
    {
        return $this->getHostDsn($config);
    }

    protected function getHostDsn(array $config)
    {
        extract($config, EXTR_SKIP);

        return isset($port)
            ? "mysql:host={$host};port={$port};dbname={$name}"
            : "mysql:host={$host};dbname={$name}";
    }

    protected function setModes(PDO $connection, array $config)
    {
        if (isset($config['modes'])) {
            $this->setCustomModes($connection, $config);
        } elseif (isset($config['strict'])) {
            if ($config['strict']) {
                $connection->prepare($this->strictMode($connection))->execute();
            } else {
                $connection->prepare("set session sql_mode='NO_ENGINE_SUBSTITUTION'")->execute();
            }
        }
    }

    protected function setCustomModes(PDO $connection, array $config)
    {
        $modes = implode(',', $config['modes']);

        $connection->prepare("set session sql_mode='{$modes}'")->execute();
    }

    protected function strictMode(PDO $connection)
    {
        if (version_compare($connection->getAttribute(PDO::ATTR_SERVER_VERSION), '8.0.11') >= 0) {
            return "set session sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'";
        }

        return "set session sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'";
    }

}