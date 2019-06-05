<?php

class Database_MySqlConnection extends Database_Connection
{
    protected function getDefaultQueryGrammar()
    {
        return $this->withTablePrefix(new Database_Query_Grammars_MySqlGrammar);
    }

    protected function getDefaultPostProcessor()
    {
        return new Database_Query_Processors_MySqlProcessor;
    }

    public function bindValues($statement, $bindings)
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1, $value,
                is_int($value) || is_float($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }
}