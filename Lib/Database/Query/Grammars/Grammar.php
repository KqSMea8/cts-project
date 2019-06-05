<?php

class Database_Query_Grammars_Grammar extends Database_Grammar
{
    protected $operators = [];

    protected $selectComponents = [
        'aggregate',
        'columns',
        'from',
        'joins',
        'wheres',
        'groups',
        'havings',
        'orders',
        'limit',
        'offset',
        'unions',
        'lock',
    ];

    public function compileSelect(Database_Query_Builder $query)
    {
        $original = $query->columns;

        if (is_null($query->columns)) {
            $query->columns = ['*'];
        }

        $sql = trim($this->concatenate(
            $this->compileComponents($query)
        ));

        $query->columns = $original;

        return $sql;
    }

    protected function compileComponents(Database_Query_Builder $query)
    {
        $sql = [];

        foreach ($this->selectComponents as $component) {

            if (!is_null($query->$component)) {
                $method = 'compile'.ucfirst($component);

                $sql[$component] = $this->$method($query, $query->$component);
            }
        }

        return $sql;
    }

    protected function compileAggregate(Database_Query_Builder $query, $aggregate)
    {
        $column = $this->columnize($aggregate['columns']);

        if ($query->distinct && $column !== '*') {
            $column = 'distinct '.$column;
        }

        return 'select '.$aggregate['function'].'('.$column.') as aggregate';
    }

    protected function compileColumns(Database_Query_Builder $query, $columns)
    {
        if (!is_null($query->aggregate)) {
            return;
        }

        $select = $query->distinct ? 'select distinct ' : 'select ';

        return $select.$this->columnize($columns);
    }

    protected function compileFrom(Database_Query_Builder $query, $table)
    {
        return 'from '.$this->wrapTable($table);
    }

    protected function compileWheres(Database_Query_Builder $query)
    {
        if (is_null($query->wheres)) {
            return '';
        }

        if (count($sql = $this->compileWheresToArray($query)) > 0) {
            return $this->concatenateWhereClauses($query, $sql);
        }

        return '';
    }

    protected function compileWheresToArray($query)
    {
        $wheres = array_map(function($where) use($query) {
            return $where['boolean'].' '.$this->{"where{$where['type']}"}($query, $where);
        }, $query->wheres);

        return $wheres;
    }

    protected function concatenateWhereClauses($query, $sql)
    {
        //$conjunction = $query instanceof JoinClause ? 'on' : 'where';
        $conjunction = 'where';

        return $conjunction.' '.$this->removeLeadingBoolean(implode(' ', $sql));
    }

    protected function whereNull(Database_Query_Builder $query, $where)
    {
        return $this->wrap($where['column']).' is null';
    }

    protected function whereNotNull(Database_Query_Builder $query, $where)
    {
        return $this->wrap($where['column']).' is not null';
    }

    protected function whereColumn(Database_Query_Builder $query, $where)
    {
        return $this->wrap($where['first']).' '.$where['operator'].' '.$this->wrap($where['second']);
    }

    protected function whereIn(Database_Query_Builder $query, $where)
    {
        if (!empty($where['values'])) {
            return $this->wrap($where['column']).' in ('.$this->parameterize($where['values']).')';
        }

        return '0 = 1';
    }

    protected function whereBasic(Database_Query_Builder $query, $where)
    {
        $value = $this->parameter($where['value']);

        return $this->wrap($where['column']).' '.$where['operator'].' '.$value;
    }

    protected function whereNested(Database_Query_Builder $query, $where)
    {
        //$offset = $query instanceof JoinClause ? 3 : 6;

        $offset = 6;

        return '('.substr($this->compileWheres($where['query']), $offset).')';
    }

    protected function whereDate(Database_Query_Builder $query, $where)
    {
        return $this->dateBasedWhere('date', $query, $where);
    }

    protected function whereTime(Database_Query_Builder $query, $where)
    {
        return $this->dateBasedWhere('time', $query, $where);
    }

    protected function whereDay(Database_Query_Builder $query, $where)
    {
        return $this->dateBasedWhere('day', $query, $where);
    }

    protected function whereMonth(Database_Query_Builder $query, $where)
    {
        return $this->dateBasedWhere('month', $query, $where);
    }

    protected function whereYear(Database_Query_Builder $query, $where)
    {
        return $this->dateBasedWhere('year', $query, $where);
    }

    protected function dateBasedWhere($type, Database_Query_Builder $query, $where)
    {
        $value = $this->parameter($where['value']);

        return $type.'('.$this->wrap($where['column']).') '.$where['operator'].' '.$value;
    }

    protected function compileGroups(Database_Query_Builder $query, $groups)
    {
        return 'group by '.$this->columnize($groups);
    }

    protected function compileHavings(Database_Query_Builder $query, $havings)
    {
        $sql = implode(' ', array_map([$this, 'compileHaving'], $havings));

        return 'having '.$this->removeLeadingBoolean($sql);
    }

    protected function compileHaving(array $having)
    {
        if ($having['type'] === 'Raw') {
            return $having['boolean'].' '.$having['sql'];
        }

        return $this->compileBasicHaving($having);
    }

    protected function compileBasicHaving($having)
    {
        $column = $this->wrap($having['column']);

        $parameter = $this->parameter($having['value']);

        return $having['boolean'].' '.$column.' '.$having['operator'].' '.$parameter;
    }

    protected function compileOrders(Database_Query_Builder $query, $orders)
    {
        if (! empty($orders)) {
            return 'order by '.implode(', ', $this->compileOrdersToArray($query, $orders));
        }

        return '';
    }

    protected function compileOrdersToArray(Database_Query_Builder $query, $orders)
    {
        return array_map(function ($order) {
            return ! isset($order['sql'])
                ? $this->wrap($order['column']).' '.$order['direction']
                : $order['sql'];
        }, $orders);
    }

    protected function compileLimit(Database_Query_Builder $query, $limit)
    {
        return 'limit '.(int) $limit;
    }

    protected function compileOffset(Database_Query_Builder $query, $offset)
    {
        return 'offset '.(int) $offset;
    }

    protected function compileLock(Database_Query_Builder $query, $value)
    {
        return is_string($value) ? $value : '';
    }

    public function compileExists(Database_Query_Builder $query)
    {
        $select = $this->compileSelect($query);

        return "select exists({$select}) as {$this->wrap('exists')}";
    }

    public function compileInsert(Database_Query_Builder $query, array $values)
    {
        $table = $this->wrapTable($query->from);

        if (!is_array(reset($values))) {
            $values = [$values];
        }

        $columns = $this->columnize(array_keys(reset($values)));

        $parameters =  array_map(function($record) {
            return '('.$this->parameterize($record).')';
        }, $values);

        $parameters = implode(',', $parameters);

        return "insert into $table ($columns) values $parameters";
    }

    public function compileInsertGetId(Database_Query_Builder $query, $values, $sequence)
    {
        return $this->compileInsert($query, $values);
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

        $wheres = $this->compileWheres($query);

        return trim("update {$table}{$joins} set $columns $wheres");
    }

    public function prepareBindingsForUpdate(array $bindings, array $values)
    {
        $cleanBindings = $bindings;

        foreach (['join', 'select'] as $v) {
            if (array_key_exists($v, $cleanBindings)) {
                unset($cleanBindings[$v]);
            }
        }


        return array_values(
            array_merge($bindings['join'], $values, static::flatten($cleanBindings))
        );
    }

    public function compileDelete(Database_Query_Builder $query)
    {
        $wheres = is_array($query->wheres) ? $this->compileWheres($query) : '';

        return trim("delete from {$this->wrapTable($query->from)} $wheres");
    }

    public function prepareBindingsForDelete(array $bindings)
    {
        return static::flatten($bindings);
    }

    public function compileTruncate(Builder $query)
    {
        return ['truncate '.$this->wrapTable($query->from) => []];
    }

    protected function concatenate($segments)
    {
        return implode(' ', array_filter($segments, function ($value) {
            return (string) $value !== '';
        }));
    }

    public function columnize(array $columns)
    {
        return implode(', ', array_map([$this, 'wrap'], $columns));
    }

    public function parameterize(array $values)
    {
        return implode(', ', array_map([$this, 'parameter'], $values));
    }

    public function parameter($value)
    {
        return $this->isExpression($value) ? $this->getValue($value) : '?';
    }

    protected function removeLeadingBoolean($value)
    {
        return preg_replace('/and |or /i', '', $value, 1);
    }

    public function getOperators()
    {
        return $this->operators;
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

    public static function flatten($array, $depth = INF)
    {
        $result = [];

        foreach ($array as $item) {
            $item = $item instanceof Collection ? $item->all() : $item;

            if (! is_array($item)) {
                $result[] = $item;
            } elseif ($depth === 1) {
                $result = array_merge($result, array_values($item));
            } else {
                $result = array_merge($result, static::flatten($item, $depth - 1));
            }
        }

        return $result;
    }
}
