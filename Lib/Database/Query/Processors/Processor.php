<?php

class Database_Query_Processors_Processor
{
    public function processSelect(Builder $query, $results)
    {
        return $results;
    }

    public function processInsertGetId(Database_Query_Builder $query, $sql, $values, $sequence = null)
    {
        $query->getConnection()->insert($sql, $values);

        $id = $query->getConnection()->getPdo()->lastInsertId($sequence);

        return is_numeric($id) ? (int) $id : $id;
    }

    public function processColumnListing($results)
    {
        return $results;
    }
}