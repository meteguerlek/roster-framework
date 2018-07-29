<?php

namespace Roster\Database;

use Closure;
use PDO;
use Roster\Logger\Log;
use Roster\Support\Arr;
use Roster\Http\Request;
use Roster\Support\Collection;
use Roster\Database\Builds\BuildsQueries;

class QueryBuilder
{
    use BuildsQueries;

    /**
     * @var PDO
     */
    public $connection;

    /**
     * @var Grammer
     */
    public $grammer;

    /**
     * @var null|string
     */
    public $sql = null;

    /**
     * @var string
     */
    public $select = ['*'];

    /**
     * @var null
     */
    public $table = null;

    /**
     * @var array
     */
    public $condition = [];

    /**
     * @var array
     */
    public $unions = [];

    /**
     * @var bool
     */
    public $union = false;

    /**
     * @var bool
     */
    public $haveWhere = false;

    /**
     * @var string
     */
    public $defaultColumn = "id";

    /**
     * @var array
     */
    public $with = [];

    /**
     * @var Model
     */
    public $model;

    /**
     * @var array
     */
    public $bindings = [];

    /**
     * @var array
     */
    public $operators = [
        '=', '!=', '<>', '>', '<', '>=',
        '>=', '<=', '!<', '!>', 'all', 'any',
        'beetween', 'exists', 'in', 'like', 'not',
        'or', 'is null', 'unique', 'not between',
        'not in', 'not like', 'is not null'
    ];

    /**
     * QueryBuilder constructor.
     *
     * @param $model
     */
    public function __construct($model = null)
    {
        $this->setConnection();

        $this->setGrammer();

        if ($model)
        {
            $this->model = $model;

            $this->table = $model->getTable();
        }
    }

    /**
     * Set table manualy
     *
     * @param $table
     * @return $this
     */
    public function table($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Set table manualy
     *
     * @param $table
     * @return $this
     */
    public function from($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Get from sql
     *
     * @param $sql
     * @param array $bindings
     * @return Collection
     */
    public function query($sql, $bindings = [])
    {
        $this->bindings = $bindings;

        $this->sql = $sql;

        return $this->collection();
    }

    /**
     * Select
     *
     * @param $select
     * @return $this
     */
    public function select($select)
    {
        $this->select = is_array($select) ? $select : func_get_args();

        return $this;
    }

    /**
     * Where clause
     *
     * @param $column
     * @param $value
     * @param string $operator
     * @return $this
     */
    public function where($column, $operator = '=', $value = null)
    {
        if (!in_array($operator, $this->operators, true))
        {
            $value = $operator;
            $operator = "=";
        }

        $this->condition[] = !$this->haveWhere
            ? "where $column $operator ?"
            : "and $column $operator ?";

        $this->bindings[] = $value;

        $this->haveWhere = true;

        return $this;
    }

    /**
     * @param Closure $closure
     * @return $this
     */
    public function whereNotExists(Closure $closure)
    {
        $newQuery = new $this();

        $closure($newQuery);

        $this->condition[] = !$this->haveWhere
            ? "where not exists ({$this->grammer->compileSelect($newQuery)})"
            : "and not exists ({$this->grammer->compileSelect($newQuery)})";

        $this->haveWhere = true;

        $this->bindings += $newQuery->bindings;

        return $this;
    }

    /**
     * Where in
     *
     * @param $column
     * @param array $values
     * @return $this
     */
    public function whereIn($column, array $values)
    {
        $values = implode("','", $values);

        $this->condition[] = !$this->haveWhere
            ? "where $column in ('$values')"
            : "and $column in ('$values')";

        $this->haveWhere = true;

        return $this;

    }

    /**
     * Is Null
     * Where clause
     *
     * @param $column
     * @return $this
     */
    public function whereNull($column)
    {
        $this->condition[] = !$this->haveWhere
            ? "where $column is null"
            : "and $column is null";

        $this->haveWhere = true;

        return $this;
    }

    /**
     * Is not null
     * Where clause
     *
     * @param $column
     * @return $this
     */
    public function whereNotNull($column)
    {
        $this->condition[] = !$this->haveWhere
            ? "where $column is not null"
            : "and $column is not null";

        $this->haveWhere = true;

        return $this;
    }

    /**
     * Or
     *
     * @param $column
     * @param $value
     * @param string $operator
     * @return $this
     */
    public function orWhere($column, $operator = '=', $value = null)
    {
        if (!in_array($operator, $this->operators, true))
        {
            $value = $operator;
            $operator = "=";
        }

        $this->condition[] = "or $column $operator ?";

        $this->bindings[] = $value;

        return $this;
    }

    public function whereRaw($raw)
    {
        $this->condition[] = !$this->haveWhere
            ? "where $raw"
            : "and $raw";

        $this->haveWhere = true;

        return $this;
    }

    /**
     * Update
     *
     * @param array $columns
     * @return $this
     */
    public function update(array $columns)
    {
        $this->sql = $this->grammer->compileUpdate($this, $columns);

        array_unshift($this->bindings, ...array_values($columns));

        return $this->save();
    }

    /**
     * Delete
     *
     * @return PDO
     */
    public function delete()
    {
        $this->sql = $this->grammer->compileDelete($this->table, $this->condition);
        $query = $this->prepare();

        if ($query->error)
        {
            return false;
        }

        return true;
    }

    /**
     * Creaet
     *
     * @param array $columns
     * @return mixed
     */
    public function create(array $columns)
    {
        $this->sql = $this->grammer->compileInsert($this->table, $columns);

        $this->bindings += array_values($columns);

        $query = $this->save();

        $model = $this->model;

        if (!$query->error)
        {
            $model->setWhereValue($query->id);
            $model->setAttributes($model->getWhere(), $query->id);

            return $model;
        }

        return false;
    }

    /**
     * Update or create
     *
     * @param array $attributes
     * @param array $columns
     * @return QueryBuilder
     */
    public function updateOrCreate(array $attributes, array $columns)
    {
        $first = clone $this;

        foreach ($attributes as $column => $value)
        {
            $first->where($column, $value);
        }

        if ($first->first())
        {
            foreach ($attributes as $column => $value)
            {
                $this->where($column, $value);
            }

            return $this->update($columns);
        }

        return $this->create($columns);
    }

    /**
     * @param Closure $closure
     * TODO
     * @return mixed
     */
    public function transaction(Closure $closure)
    {
        $this->connection->beginTransaction();

        $callback = $closure($this);

        $this->connection->rollBack();

        return $callback;
    }

    /**
     * Order by
     *
     * @param $column
     * @param string $sort
     * @return $this
     */
    public function orderBy($column, $sort = 'asc')
    {
        $this->condition[] = "order by $column $sort";

        return $this;
    }

    /**
     * ASC
     *
     * @param string $column
     * @return $this
     */
    public function asc($column = 'id')
    {
        $this->condition[] = "order by $column asc";

        return $this;
    }

    /**
     * DESC
     *
     * @param string $column
     * @return $this
     */
    public function desc($column = 'id')
    {
        $this->condition[] = "order by $column desc";

        return $this;
    }

    /**
     * Offset
     *
     * @param $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->condition[] = "offset $offset";

        return $this;
    }

    /**
     * Group by
     *
     * @param $by
     * @return $this
     */
    public function groupBy($by)
    {
        $this->condition[] = "group by $by";

        return $this;
    }

    /**
     * Having
     *
     * @param $condition
     * @return $this
     */
    public function having($condition)
    {
        $this->condition[] = "having $condition" ;

        return $this;
    }

    /**
     * Set limit
     *
     * @param $limit
     * @return $this
     */
    public function limit($limit)
    {
        $this->condition[] = "limit $limit";

        return $this;
    }

    /**
     * @return Model
     */
    public function random()
    {
        $this->orderBy('RAND()');

        return $this->first();
    }

    /**
     * Join
     *
     * @param $table
     * @param $table1
     * @param null $operator
     * @param $table2
     * @param string $type
     * @return QueryBuilder
     */
    public function join($table, $table1, $operator = null, $table2 = null, $type = 'inner')
    {
        $table2 = "$operator $table2";

        if (!in_array($operator, $this->operators, true))
        {
            $table2 = "= $operator";
        }

        $this->condition[] = "$type join $table on $table1 $table2";

        return $this;
    }

    /**
     * Inner join
     *
     * @param $table
     * @param $table1
     * @param null $operator
     * @param $table2
     * @return QueryBuilder
     */
    public function innerJoin($table, $table1, $operator = null, $table2 = null)
    {
        return $this->join($table, $table1, $operator, $table2, 'inner');
    }

    /**
     * Left join
     *
     * @param $table
     * @param $table1
     * @param null $operator
     * @param $table2
     * @return QueryBuilder
     */
    public function leftJoin($table, $table1, $operator = null, $table2 = null)
    {
        return $this->join($table, $table1, $operator, $table2, 'left');
    }

    /**
     * Rigth join
     *
     * @param $table
     * @param $table1
     * @param null $operator
     * @param $table2
     * @return QueryBuilder
     */
    public function rightJoin($table, $table1, $operator = null, $table2 = null)
    {
        return $this->join($table, $table1, $operator, $table2, 'right');
    }

    /**
     * Cross join
     *
     * @param $table
     * @param $table1
     * @param null $operator
     * @param $table2
     * @return QueryBuilder
     */
    public function crossJoin($table, $table1, $operator = null, $table2 = null)
    {
        return $this->join($table, $table1, $operator, $table2, 'cross');
    }

    /**
     * Union
     *
     * @param $queryBuilder
     * @param string $union
     * @return $this
     * @throws \Exception
     */
    public function union($queryBuilder, $union = '')
    {
        if ($queryBuilder instanceof QueryBuilder)
        {
            $this->unions[$union] = $queryBuilder;

            return $this;
        }

        throw new \Exception("Argument 1 must be an instance of QueryBuilder");
    }

    /**
     * Get results with model as object
     *
     * @return Collection
     */
    public function get()
    {
        $this->sql = $this->grammer->compileSelect($this);

        return $this->collection();
    }

    /**
     * Get first model
     *
     * @return Model
     */
    public function first()
    {
        $this->limit(1);

        $this->sql = $this->grammer->compileSelect($this);

        if ($this->model instanceof DB)
        {
            return $this->fetch(PDO::FETCH_OBJ);
        }

        if ($fetch = $this->fetch())
        {
            return $this->getModel($fetch);
        }

        return false;
    }

    /**
     * Get first model
     *
     * @param string $column
     * @return Model
     */
    public function lastest($column = 'id')
    {
        $this->desc($column);

        return $this->first();
    }

    /**
     * Get all from table
     *
     * @param array $columns
     * @return Collection
     */
    public function all($columns = ['*'])
    {
        $this->select = $columns;

        return $this->get();
    }

    /**
     * Find from id or specific
     *
     * @param $ids
     * @param string $where
     * @return Model
     */
    public function find($ids, $where = '')
    {
        $column = $where ? $where : $this->defaultColumn;

        $this->where($column, $ids);

        return is_array($ids)
            ? $this->get()
            : $this->first();
    }

    /**
     * Delete from id
     *
     * @param $ids
     * @param string $where
     * @return PDO
     */
    public function destroy($ids, $where = '')
    {
        $column = $where ? $where : $this->defaultColumn;

        $this->where($column, $ids);

        return $this->delete();
    }

    /**
     * Save updates
     *
     * @return mixed
     */
    protected function save()
    {
        $query = $this->prepare();
        $query->id = $this->getConnection()->lastInsertId();

        return $query;
    }

    /**
     * Count results
     *
     * @return mixed
     */
    public function count()
    {
        $clone = clone $this;

        $clone->select = ['count(*) as counter'];

        $clone->sql = $clone->grammer->compileSelect($clone);

        return (int) $clone->fetch()['counter'];
    }

    /**
     * Sum columns
     *
     * @param $column
     * @return int
     */
    public function sum($column)
    {
        $clone = clone $this;

        $clone->select = ['sum('.$column.') as total'];

        $clone->sql = $clone->grammer->compileSelect($clone);

        return $clone->fetch()['total'];
    }

    /**
     * Subtime
     *
     * @param $column
     * @return int
     */
    public function subtime($column)
    {
        $clone = clone $this;

        $clone->select = ['subtime('.$column.') as total'];

        $clone->sql = $clone->grammer->compileSelect($clone);

        return (int) $clone->fetch['total'];
    }

    /**
     * Generate Paginate
     *
     * @param $perPage
     * @return Collection
     */
    public function paginate($perPage)
    {
        $currentPage = Request::has('page') ? Request::getValue('page') : 0;

        $total = $this->count();

        $lastPage = ceil($total / $perPage);

        if ($currentPage <= 1)
        {
            $currentPage = 0;
        }
        elseif ($currentPage > $lastPage)
        {
            $currentPage = $lastPage - 1;
        }
        else
        {
            $currentPage = $currentPage - 1;
        }

        $this->with['paginate'] = $this->paginator($total, $perPage, $currentPage, $lastPage);

        return $this->limit($currentPage * $perPage.','. $perPage)->get();
    }

    /**
     * Get models, if more results
     *
     * @param $results
     * @return array
     */
    protected function getModels($results)
    {
        return array_map(function ($result) {
            return $this->getModel($result);
        }, Arr::isMultiple($results) ? $results : count($results) ? $results : []);
    }

    /**
     * Get model, if only one result
     *
     * @param $result
     * @return Model
     */
    protected function getModel($result)
    {
        $model = new $this->model();
        $model->setAttributes($result ? $result : []);
        $model->setWhereValue(isset($result[$model->getWhere()]) ? $result[$model->getWhere()] : false);

        return $model;
    }

    /**
     * Rrepare sql
     *
     * @return mixed
     */
    protected function prepare()
    {
        $query = $this->getConnection()
            ->prepare($this->sql);

        $this->grammer->bindValues($query, $this->bindings);

        $query->execute();

        $query->error = $query->errorInfo()[2] ? true : false;

        return $query;
    }

    /**
     * Fetch
     *
     * @param int $style
     * @return mixed
     */
    protected function fetch($style = PDO::FETCH_ASSOC)
    {
        return $this->prepare()->fetch($style);
    }

    /**
     * Fetch all
     *
     * @param int $style
     * @return mixed
     */
    protected function fetchAll($style = PDO::FETCH_ASSOC)
    {
        return $this->prepare()->fetchAll($style);
    }

    /**
     * @param int $style
     * @return mixed
     */
    protected function fetchColumn($style = PDO::FETCH_ASSOC)
    {
        return $this->prepare()->fetchColumn($style);
    }

    /**
     * @param $statement
     * @return mixed
     */
    public function statement($statement)
    {
        $this->sql = $statement;

        return $this->fetchAll();
    }

    /**
     * @return mixed
     */
    public function truncate()
    {
        $this->sql = "truncate table {$this->table}";

        return $this->fetchAll();
    }

    /**
     * @param $raw
     * @return QueryBuilder
     */
    public function raw($raw)
    {
        return $this->whereRaw($raw);
    }

    /**
     * Collection
     *
     * @return Collection
     */
    protected function collection()
    {
        return $this->model instanceof DB
            ? Collection::make($this->fetchAll())->with($this->with)
            : Collection::make($this->getModels($this->fetchAll()))->with($this->with);
    }

    /**
     * Comming soon
     *
     * @param $column
     * @return string
     */
    public function wrap($column)
    {
        return '`'.implode('`.`', explode('.', $column)).'`';
    }

    /**
     * Get sql
     *
     * @param string $query
     * @return bool|null|string
     */
    public function getSql($query = 'select')
    {
        if ($query == 'select')
        {
            return $this->grammer->compileSelect($this);
        }
        elseif ($query == 'delete')
        {
            return $this->grammer->compileDelete($this->table, $this->condition);
        }
        elseif ($query == 'update' && $query == 'insert')
        {
            return $this->sql;
        }

        return false;
    }

    /**
     * Debug
     */
    protected function debug()
    {
        ob_start();

        $this->prepare()->debugDumpParams();

        return dd(ob_get_clean());
    }

    /**
     * @return QueryBuilder
     */
    public function copy()
    {
        return clone $this;
    }

    /**
     * Set Conneciton
     *
     * @return $this
     */
    private function setConnection()
    {
        $instance = Connection::getInstance();

        return $this->connection = $instance->getConnection();
    }

    /**
     * Get connection
     * 
     * @return PDO
     */
    private function getConnection()
    {
        return $this->connection;
    }

    /**
     * Set Grammer
     *
     * @return Grammer
     */
    protected function setGrammer()
    {
        return $this->grammer = new Grammer();
    }
}