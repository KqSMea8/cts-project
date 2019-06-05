<?php

class Database_QueryException extends PDOException
{
    protected $sql;

    protected $bindings;

    public function __construct($sql, array $bindings, $previous)
    {
        parent::__construct('', 0, $previous);

        $this->sql = $sql;
        $this->bindings = $bindings;
        $this->code = $previous->getCode();
        $this->message = $this->formatMessage($sql, $bindings, $previous);

        if ($previous instanceof PDOException) {
            $this->errorInfo = $previous->errorInfo;
        }
    }

    protected function formatMessage($sql, $bindings, $previous)
    {
        return $previous->getMessage().' (SQL: '.static::replaceArray('?', $bindings, $sql).')';
    }

    public function getSql()
    {
        return $this->sql;
    }

    public function getBindings()
    {
        return $this->bindings;
    }

    protected function replaceArray($search, array $replace, $subject)
    {
        foreach ($replace as $value) {
            $subject = static::replaceFirst($search, $value, $subject);
        }

        return $subject;
    }

    public static function replaceFirst($search, $replace, $subject)
    {
        if ($search == '') {
            return $subject;
        }

        $position = strpos($subject, $search);

        if ($position !== false) {
            return substr_replace($subject, $replace, $position, strlen($search));
        }

        return $subject;
    }
}
