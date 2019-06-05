<?php

trait Lib_Traits_Redis
{
    protected static $connection;

    public static function connection($name)
    {
        if (is_null(static::$connection)) {
            static::$connection = new Redis2($name);
        }

        return static::$connection;
    }
}