<?php

abstract class Database_Grammar
{
    protected $tablePrefix = '';

    public function wrapTable($table)
    {
        if (!$this->isExpression($table)) {
            return $this->wrap($this->tablePrefix.$table, true);
        }

        return $this->getValue($table);
    }

    public function wrap($value, $prefixAlias = false)
    {
        if ($this->isExpression($value)) {
            return $this->getValue($value);
        }

        if (stripos($value, ' as ') !== false) {
            return $this->wrapAliasedValue($value, $prefixAlias);
        }

        return $this->wrapSegments(explode('.', $value));
    }

    protected function wrapAliasedValue($value, $prefixAlias = false)
    {
        $segments = preg_split('/\s+as\s+/i', $value);

        if ($prefixAlias) {
            $segments[1] = $this->tablePrefix.$segments[1];
        }

        return $this->wrap(
            $segments[0]).' as '.$this->wrapValue($segments[1]
            );
    }

    protected function wrapSegments($segments)
    {

        $segments = array_map(function ($segment, $key) use ($segments) {
            return $key == 0 && count($segments) > 1
                        ? $this->wrapTable($segment)
                        : $this->wrapValue($segment);
        }, $segments, array_keys($segments));

        return implode('.', $segments);
    }

    protected function wrapValue($value)
    {
        if ($value !== '*') {
            return '"'.str_replace('"', '""', $value).'"';
        }

        return $value;
    }

    public function parameter($value)
    {
        return $this->isExpression($value) ? $this->getValue($value) : '?';
    }

    public function isExpression($value)
    {
        return $value instanceof Database_Query_Expression;
    }

    public function getValue($expression)
    {
        return $expression->getValue();
    }

    public function getDateFormat()
    {
        return 'Y-m-d H:i:s';
    }

    public function setTablePrefix($prefix)
    {
        $this->tablePrefix = $prefix;

        return $this;
    }

    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }
}