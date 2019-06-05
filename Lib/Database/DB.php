<?php

class Database_DB
{
    protected static $instance;

    public static function __callStatic($method, $args)
    {
        $instance = static::resolveInstance();

        return $instance->$method(...$args);
    }

    public static function resolveInstance()
    {
        if (!is_null(static::$instance)) {
            return static::$instance;
        }

        $factory = new Database_ConnectionFactory;
        $manager = new Database_DatabaseManager($factory);

        static::$instance = $manager;

        return static::$instance;
    }
}