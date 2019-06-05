<?php

class Database_Query_Grammars_MySqlGrammar extends Database_Query_Grammars_Grammar
{
    protected function wrapValue($value)
    {
        return $value === '*' ? $value : '`'.str_replace('`', '``', $value).'`';
    }

    public function compileUpdate(Database_Query_Builder $query, $values)
    {
        $table = $this->wrapTable($query->from);

        $columns = array_map(function($value, $key) {
            return $this->wrap($key).' = '.$this->parameter($value);
        }, $values, array_keys($values));

        $columns = implode(',', $columns);

        $joins = '';
        /*
        if (isset($query->joins)) {
            $joins = ' '.$this->compileJoins($query, $query->joins);
        }
        */
        $where = $this->compileWheres($query);

        $sql = rtrim("update {$table}{$joins} set $columns $where");

        if (! empty($query->orders)) {
            $sql .= ' '.$this->compileOrders($query, $query->orders);
        }

        if (isset($query->limit)) {
            $sql .= ' '.$this->compileLimit($query, $query->limit);
        }

        return rtrim($sql);
    }

    public function compileDelete(Database_Query_Builder $query)
    {
        $table = $this->wrapTable($query->from);

        $where = is_array($query->wheres) ? $this->compileWheres($query) : '';

        return isset($query->joins)
            ? $this->compileDeleteWithJoins($query, $table, $where)
            : $this->compileDeleteWithoutJoins($query, $table, $where);
    }

    protected function compileDeleteWithoutJoins($query, $table, $where)
    {
        $sql = trim("delete from {$table} {$where}");

        if (! empty($query->orders)) {
            $sql .= ' '.$this->compileOrders($query, $query->orders);
        }

        if (isset($query->limit)) {
            $sql .= ' '.$this->compileLimit($query, $query->limit);
        }

        return $sql;
    }

    protected function compileDeleteWithJoins($query, $table, $where)
    {
        throw new Database_QueryException('Not support join delete!');
    }

}