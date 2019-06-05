<?php

class Database_Query_Processors_MySqlProcessor extends Database_Query_Processors_Processor
{
    public function processColumnListing($results)
    {
        return array_map(function ($result) {
            return ((object) $result)->column_name;
        }, $results);
    }
}