<?php

class Database_Connection
{
    use Database_DetectsLostConnections;

    protected $pdo;

    protected $readPdo;

    protected $database;

    protected $tablePrefix = '';

    protected $config = [];

    protected $reconnector;

    protected $queryGrammar;

    protected $postProcessor;

    protected $fetchMode = PDO::FETCH_ASSOC;

    protected $transactions = 0;

    protected $recordsModified = false;

    protected $queryLog = [];

    protected $loggingQueries = false;

    protected $pretending = false;

    public function __construct($pdo, $database = '', $tablePrefix = '', array $config = [])
    {
        $this->pdo = $pdo;

        $this->database = $database;

        $this->tablePrefix = $tablePrefix;

        $this->config = $config;

        $this->useDefaultQueryGrammar();

        $this->useDefaultPostProcessor();
    }

    public function useDefaultQueryGrammar()
    {
        $this->queryGrammar = $this->getDefaultQueryGrammar();
    }

    protected function getDefaultQueryGrammar()
    {
        return new Database_Query_Grammars_Grammar;
    }

    public function useDefaultPostProcessor()
    {
        $this->postProcessor = $this->getDefaultPostProcessor();
    }

    protected function getDefaultPostProcessor()
    {
        return new Database_Query_Processors_Processor;
    }

    public function table($table)
    {
        return $this->query()->from($table);
    }

    public function query()
    {
        return new Database_Query_Builder(
            $this, $this->getQueryGrammar(), $this->getPostProcessor()
        );
    }

    public function setReadPdo($pdo)
    {
        $this->readPdo = $pdo;

        return $this;
    }

    public function getQueryGrammar()
    {
        return $this->queryGrammar;
    }

    public function getPostProcessor()
    {
        return $this->postProcessor;
    }

    public function pretending()
    {
        return $this->pretending === true;
    }

    public function select($query, $bindings = [], $useReadPdo = true)
    {
        return $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending()) {
                return [];
            }

            $statement = $this->prepared($this->getPdoForSelect($useReadPdo)
                                         ->prepare($query));

            $this->bindValues($statement, $this->prepareBindings($bindings));

            $statement->execute();

            return $statement->fetchAll();
        });
    }

    public function cursor($query, $bindings = [], $useReadPdo = true)
    {
        $statement = $this->run($query, $bindings, function ($query, $bindings) use ($useReadPdo) {
            if ($this->pretending()) {
                return [];
            }

            $statement = $this->prepared($this->getPdoForSelect($useReadPdo)
                              ->prepare($query));

            $this->bindValues(
                $statement, $this->prepareBindings($bindings)
            );

            $statement->execute();

            return $statement;
        });

        while ($record = $statement->fetch()) {
            yield $record;
        }
    }

    protected function run($query, $bindings, Closure $callback)
    {
        $this->reconnectIfMissingConnection();

        $start = microtime(true);

        try {
            $result = $this->runQueryCallback($query, $bindings, $callback);
        } catch (Database_QueryException $e) {
            $result = $this->handleQueryException(
                $e, $query, $bindings, $callback
            );
        }

        $this->logQuery(
            $query, $bindings, $this->getElapsedTime($start)
        );

        return $result;
    }

    protected function runQueryCallback($query, $bindings, Closure $callback)
    {
        try {
            $result = $callback($query, $bindings);
        } catch (Exception $e) {
            throw new Database_QueryException(
                $query, $this->prepareBindings($bindings), $e
            );
        }

        return $result;
    }

    public function logQuery($query, $bindings, $time = null)
    {
        if ($this->loggingQueries) {
            $this->queryLog[] = compact('query', 'bindings', 'time');
        }
    }

    protected function getElapsedTime($start)
    {
        return round((microtime(true) - $start) * 1000, 2);
    }

    protected function handleQueryException($e, $query, $bindings, Closure $callback)
    {
        if ($this->transactions >= 1) {
            throw $e;
        }

        return $this->tryAgainIfCausedByLostConnection(
            $e, $query, $bindings, $callback
        );
    }

    protected function tryAgainIfCausedByLostConnection(Database_QueryException $e, $query, $bindings, Closure $callback)
    {
        if ($this->causedByLostConnection($e->getPrevious())) {
            $this->reconnect();

            return $this->runQueryCallback($query, $bindings, $callback);
        }

        throw $e;
    }

    protected function reconnectIfMissingConnection()
    {
        if (is_null($this->pdo)) {
            $this->reconnect();
        }
    }

    public function reconnect()
    {
        if (is_callable($this->reconnector)) {
            return call_user_func($this->reconnector, $this);
        }

        throw new LogicException('Lost connection and no reconnector available.');
    }

    public function disconnect()
    {
        $this->setPdo(null)->setReadPdo(null);
    }

    protected function prepared(PDOStatement $statement)
    {
        $statement->setFetchMode($this->fetchMode);

        return $statement;
    }

    protected function getPdoForSelect($useReadPdo = true)
    {
        return $useReadPdo ? $this->getReadPdo() : $this->getPdo();
    }

    public function getReadPdo()
    {
        if ($this->transactions > 0) {
            return $this->getPdo();
        }

        /*
        if ($this->recordsModified && $this->getConfig('sticky')) {
            return $this->getPdo();
        }
        */

        if ($this->readPdo instanceof Closure) {
            return $this->readPdo = call_user_func($this->readPdo);
        }

        return $this->readPdo ?: $this->getPdo();
    }

    public function getPdo()
    {
        if ($this->pdo instanceof Closure) {
            return $this->pdo = call_user_func($this->pdo);
        }

        return $this->pdo;
    }

    public function insert($query, $bindings = [])
    {
        return $this->statement($query, $bindings);
    }

    public function update($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    public function delete($query, $bindings = [])
    {
        return $this->affectingStatement($query, $bindings);
    }

    public function statement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return true;
            }

            $statement = $this->getPdo()->prepare($query);

            $this->bindValues($statement, $this->prepareBindings($bindings));

            $this->recordsHaveBeenModified();

            return $statement->execute();
        });
    }

    public function affectingStatement($query, $bindings = [])
    {
        return $this->run($query, $bindings, function ($query, $bindings) {
            if ($this->pretending()) {
                return 0;
            }

            $statement = $this->getPdo()->prepare($query);

            $this->bindValues($statement, $this->prepareBindings($bindings));

            $statement->execute();

            $this->recordsHaveBeenModified(
                ($count = $statement->rowCount()) > 0
            );

            return $count;
        });
    }

    public function bindValues($statement, $bindings)
    {
        foreach ($bindings as $key => $value) {
            $statement->bindValue(
                is_string($key) ? $key : $key + 1, $value,
                is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR
            );
        }
    }

    public function prepareBindings(array $bindings)
    {
        $grammar = $this->getQueryGrammar();

        foreach ($bindings as $key => $value) {

            if ($value instanceof DateTimeInterface) {
                $bindings[$key] = $value->format($grammar->getDateFormat());
            } elseif (is_bool($value)) {
                $bindings[$key] = (int) $value;
            }
        }

        return $bindings;
    }

    public function getTablePrefix()
    {
        return $this->tablePrefix;
    }

    public function withTablePrefix(Database_Grammar $grammar)
    {
        $grammar->setTablePrefix($this->tablePrefix);

        return $grammar;
    }

    public function setTablePrefix($prefix)
    {
        $this->tablePrefix = $prefix;

        $this->getQueryGrammar()->setTablePrefix($prefix);
    }

    public function getQueryLog()
    {
        return $this->queryLog;
    }

    public function enableQueryLog()
    {
        $this->loggingQueries = true;
    }

    public function disableQueryLog()
    {
        $this->loggingQueries = false;
    }

    public function logging()
    {
        return $this->loggingQueries;
    }

    public function raw($value)
    {
        return new Database_Query_Expression($value);
    }

    public function recordsHaveBeenModified($value = true)
    {
        if (!$this->recordsModified) {
            $this->recordsModified = $value;
        }
    }
}