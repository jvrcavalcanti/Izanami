<?php

declare(strict_types = 1);

namespace Accolon\Izanami;

use Accolon\Izanami\DB;
use Accolon\Izanami\Exceptions\FailQueryException;
use Accolon\Izanami\Exceptions\ModelNotFoundException;
use JsonSerializable;
use Accolon\Izanami\Interfaces\Jsonable;
use Accolon\Izanami\Interfaces\Arrayable;
use Accolon\Logging\Log;

abstract class Model implements JsonSerializable, Jsonable, Arrayable
{
    const SELECT = 1;
    const INSERT = 2;
    const UPDATE = 3;
    const DELETE = 4;
    const COUNT = 5;

    private \ReflectionClass $reflection;

    private string $joinS = '';
    private string $limit = '';
    private string $columns = '';
    private string $offset = '';
    private string $order = '';
    private string $statement = '';
    private array $params = [];
    private int $operation = 0;
    private string $where = '';
    private array $attributes = [];
    private array $original = [];
    private bool $exists = false;

    protected string $primaryKey = 'id';
    protected bool $autoIncrement = true;

    public function __construct($attributes = [])
    {
        foreach ($attributes as $key => $value) {
            $this->$key = $value;
        }

        if (!isset($this->table)) {
            $namespace = static::class;
            $array = explode("\\", $namespace);
            $table = strtolower($array[sizeof($array) - 1]) . "s";
            $this->table = $table;
        }

        $this->reflection = new \ReflectionClass(static::class);
    }

    public function getPrimaryKey()
    {
        return $this->attributes[$this->primaryKey];
    }

    public function setTable(string $table): Model
    {
        $this->table = $table;
        return $this;
    }

    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
    }

    public static function __callStatic($name, $arguments)
    {
        return \Closure::fromCallable([new static, $name])(...$arguments);
    }

    public function __set($name, $value)
    {
        if (in_array($name, $this->exceptions())) {
            return $this->$name = $value;
        }

        return $this->attributes[$name] = $value;
    }

    public function __isset($name)
    {
        return isset($this->attributes[$name]);
    }

    public function __unset($name)
    {
        unset($this->attributes[$name]);
    }

    public function __serialize(): array
    {
        return $this->filterSensitives();
    }

    public function __unserialize(array $data): void
    {
        $this->attributes = $data;
    }

    public function jsonSerialize()
    {
        return $this->filterSensitives();
    }

    public function __toString()
    {
        return $this->jsonSerialize();
    }

    private function filterSensitives()
    {
        $sensitives = $this->sensitives ?? [];
        return array_filter(
            $this->toArray(),
            fn($attr) => !in_array($attr, $sensitives),
            ARRAY_FILTER_USE_KEY
        );
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    private static function exceptions()
    {
        return [
            'table',
            'sensitives',
            'debug',
            'attributes',
            'primaryKey',
            'autoIncrement'
        ];
    }

    public static function attributesModel()
    {
        $exceptions = static::exceptions();
        return array_filter(
            array_map(
                fn(\ReflectionProperty $prop) => $prop->getName(),
                (new \ReflectionClass(static::class))->getProperties()
            ),
            fn($prop) => !in_array($prop, $exceptions)
        );
    }

    public function persist($iterable): void
    {
        if (!is_array($iterable) && !is_object($iterable)) {
            throw new \Exception("Not's iterable");
        }

        foreach ($iterable as $attr => $value) {
            $this->attributes[$attr] = $value;
        }
        $this->original = $this->attributes;
    }

    public function clear()
    {
        $attrs = self::attributesModel();
        foreach ($attrs as $attr) {
            if (is_string($this->$attr)) {
                $this->$attr = '';
            }

            if (is_array($this->$attr)) {
                $this->$attr = [];
            }

            if (is_bool($this->$attr)) {
                $this->$attr = false;
            }
        }
    }

    public function limit(int $num): Model
    {
        $this->limit = "LIMIT {$num} ";
        return $this;
    }

    public function offset(int $num): Model
    {
        $this->offset = "OFFSET {$num} ";
        return $this;
    }

    public function order(string $col, string $order): Model
    {
        $this->order = "ORDER BY {$col} {$order} ";
        return $this;
    }

    public function asc(string $col = "id")
    {
        return $this->order($col, "ASC");
    }

    public function desc(string $col = "id")
    {
        return $this->order($col, "DESC");
    }

    public function setExists(bool $value): Model
    {
        $this->exists = $value;
        return $this;
    }

    public function count(): int
    {
        $this->select();

        $this->operation = static::COUNT;

        return $this->execute();
    }

    public static function build($data = []): Model
    {
        $obj = (new \ReflectionClass(static::class))->newInstance();

        $obj->persist($data);

        return $obj;
    }

    public static function builder()
    {
        return new Builder(static::class);
    }

    public function getStatement()
    {
        return $this->statement . $this->joinS . $this->where . $this->order . $this->limit . $this->offset;
    }

    private function execute(bool $all = true)
    {
        $db = DB::connection();

        $stmt = $db->prepare($this->getStatement());

        if (isset($this->debug) && $this->debug) {
            Log::debug(
                "SQL = " . $stmt->queryString
            );
        }

        $result = $stmt->execute($this->params);

        switch ($this->operation) {
            case Model::COUNT:
                $this->clear();
                return $stmt->rowCount();

            case Model::SELECT:
                if (!$stmt->rowCount()) {
                    $this->clear();
                    return null;
                }

                if ($all) {
                    $this->clear();
                    return $stmt->fetchAll();
                }

                $result = $stmt->fetchObject();

                $this->clear();

                return $result;

            case Model::INSERT:
                $this->id = $db->lastInsertId();

            default:
                $this->clear();
                return $result;
        }
    }

    /* ********************* Relationships ********************************/

    public function hasOne(string $class, ?string $foreignKey = null, ?string $localKey = null): Model
    {
        $reflection = new \ReflectionClass($class);
        $name = strtolower($reflection->getShortName());
        $exception = $localKey ?? "{$name}_id";

        if (isset($this->attributes[$exception])) {
            return (new $class)->where($foreignKey ?? $this->primaryKey, $this->attributes[$exception])->first();
        }

        throw new ModelNotFoundException("{$class} not found");
    }

    public function belongsToMany(string $class, ?string $foreignKey = null): Collection
    {
        $reflection = new \ReflectionClass(static::class);
        $name = strtolower($reflection->getShortName());
        $foreignKey = $foreignKey ?? "{$name}_id";
        $localKey = $this->primaryKey;

        if (isset($this->attributes[$localKey])) {
            return (new $class)->where($foreignKey, $this->attributes[$localKey])->all();
        }

        throw new ModelNotFoundException("{$class} not found");
    }

    public function belongsToOne(string $class, ?string $foreignKey = null): Model
    {
        $reflection = new \ReflectionClass($class);
        $name = strtolower($reflection->getShortName());
        $foreignKey = $foreignKey ?? $this->primaryKey;
        $localKey = "{$name}_id";

        if (isset($this->attributes[$localKey])) {
            return (new $class)->where($foreignKey, $this->attributes[$localKey])->first();
        }

        throw new ModelNotFoundException("{$class} not found");
    }

    public function hasMany(string $class, ?string $foreignKey = null, ?string $localKey = null): Collection
    {
        $reflection = new \ReflectionClass(static::class);
        $name = strtolower($reflection->getShortName());
        $localKey = $localKey ?? $this->primaryKey;
        $foreignKey = $foreignKey ?? "{$name}_id";

        if (isset($this->attributes[$localKey])) {
            return (new $class)->where($foreignKey, $this->attributes[$localKey])->all();
        }

        throw new ModelNotFoundException("{$class} not found");
    }

    public function morphToMany(string $class, string $tablePrefix, ?string $foreignKey = null, ?string $localKey = null)
    {
        $shortClass = strtolower((new \ReflectionClass($class))->getShortName()) . '_id';
        $typeField = $tablePrefix . "_type";
        $type = static::class;
        $localKey = $localKey ?? $this->primaryKey;
        $foreignKey = $tablePrefix . '_id';

        if (isset($this->attributes[$localKey])) {
            return DB::table($tablePrefix . 's')
                    ->where($typeField, $type)
                    ->where($foreignKey, $this->attributes[$localKey])
                    ->all()
                    ->map(fn (Model $model) => (new $class)->find($model->$shortClass));
        }
        
        throw new IzanamiException("morphToMany error");
    }

    public function morphedByMany(string $class, string $tablePrefix)
    {
        $localKey = $localKey ?? $this->primaryKey;
        $typeField = $tablePrefix . "_type";
        $shortLocalClass = strtolower((new \ReflectionClass(static::class))->getShortName()) . '_id';
        $foreignKey = $tablePrefix . '_id';

        return DB::table($tablePrefix . 's')
                ->where($shortLocalClass, $this->attributes[$localKey])
                ->where($typeField, $class)
                ->all()
                ->map(fn(Model $model) => (new $class)->find($model->$foreignKey));
    }

    /* ********************* CRUD *********************** */

    public function select(...$cols): Model
    {
        $this->operation = Model::SELECT;

        if (!sizeof($cols)) {
            $cols = ['*'];
        }

        $this->columns = implode(", ", $cols);

        $this->statement = "SELECT {$this->columns} FROM `{$this->table}` ";

        return $this;
    }

    public function delete(): bool
    {
        $this->operation = Model::DELETE;
        $this->statement = "DELETE FROM {$this->table} ";

        if ($this->exists) {
            foreach ($this->attributes as $key => $value) {
                $this->where($key, $value);
            }
        }

        return $this->execute();
    }

    public function create(array $data): bool
    {
        $this->operation = Model::INSERT;

        $fields = [];
        $values = [];

        if ($this->autoIncrement && isset($data[$this->primaryKey])) {
            unset($data[$this->primaryKey]);
        }

        foreach ($data as $key => $value) {
            $this->addParam($key, $value);
            $fields[] = "`{$key}`";
            $values[] = ":{$key}";
        }

        $fields = "(" . implode(", ", $fields) . ")";
        $values = "(" . implode(", ", $values) . ")";

        $this->statement = "INSERT INTO {$this->table} {$fields} VALUES {$values}";

        $result = $this->execute();

        if ($result) {
            $this->persist($data);
            $this->setExists(true);
        }

        return $result;
    }

    public function save(): bool
    {
        if ($this->exists) {
            foreach ($this->original as $key => $value) {
                $this->where($key, $value);
            }
            return $this->update($this->attributes);
        }

        return $this->create($this->attributes);
    }

    public function update(array $cols)
    {
        $this->operation = Model::UPDATE;

        $set = [];

        foreach ($cols as $key => $col) {
            $this->addParam("set" . $key, $col);
            $set[] = "{$key} = :set{$key}";
        }

        $set = implode(", ", $set);

        $this->statement = "UPDATE {$this->table} SET {$set} ";

        return $this->execute();
    }

    /* ****************************** Query ************************** */

    public function getAll(...$columns): Collection
    {
        $this->query()->select(...$columns);
            
        $result = $this->execute(true);

        if (!$result) {
            return new Collection([]);
        }

        return new Collection(array_map(
            fn($obj) => static::build($obj)->setExists(true),
            $result
        ));
    }

    public function get(...$columns)
    {
        $this->query()->select(...$columns);

        $result = $this->execute(false);

        return $result ? static::build($result)->setExists(true) : null;
    }

    public function first(...$columns)
    {
        $this->query()->select(...$columns);

        return $this->get();
    }

    public function all(...$columns)
    {
        $this->query();

        return $this->getAll(...$columns);
    }

    public function firstOrFail(...$columns)
    {
        $result = $this->first($columns);

        $this->fail($result);

        return $result;
    }

    private function fail($result)
    {
        if (!$result) {
            throw new FailQueryException("Find by Id failed");
        }
    }

    public function firstWhere(string ...$params)
    {
        return $this->query()->where(...$params)->first();
    }

    public function when(bool $result, callable $callback)
    {
        if ($result) {
            $callback($this);
        }
        return $this;
    }

    public function exists(): bool
    {
        $result = $this->query()->get();

        $this->exists = !!$result;

        return $this->exists;
    }

    public function query(): Model
    {
        $this->operation = Model::SELECT;

        if (!$this->columns || $this->columns == "") {
            $this->columns = "*";
        }

        $this->statement = "SELECT {$this->columns} FROM {$this->table} ";

        return $this;
    }

    public function addParam($param, $value)
    {
        $this->params[":{$param}"] = $value;
        return $this;
    }

    public function addParams(array $params)
    {
        foreach ($params as $param => $value) {
            $this->addParam($param, $value);
        }
        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function find($value)
    {
        return $this->query()->where($this->primaryKey, $value)->get();
    }

    public function findOrFail($value)
    {
        $result = $this->find($value);

        $this->fail($result);

        return $result;
    }

    private function join(string $type, string $table, array $params): Model
    {
        $this->join = "{$type} JOIN {$table} ON" . array_reduce(
            $params,
            fn($carry, $param) => $carry . " " . $param,
            ""
        );
        return $this;
    }

    public function innerJoin(string $table, string ...$params)
    {
        return $this->join("INNER", $table, $params);
    }

    public function leftJoin(string $table, string ...$params)
    {
        return $this->join("LEFT", $table, $params);
    }

    public function rightJoin(string $table, string ...$params)
    {
        return $this->join("RIGHT", $table, $params);
    }

    public function fullJoin(string $table, string ...$params)
    {
        return $this->join("FULL OUTER", $table, $params);
    }

    public function whereIn(string $col, array $values): Model
    {
        if (!$this->where) {
            $this->where = "WHERE ";
        } else {
            $this->where .= "AND ";
        }

        $params = [];

        foreach ($values as $key => $value) {
            $this->addParam($col . ($key + 1), $value);
            $params[] = ':' . $col . ($key + 1);
        }

        $params = "(" . implode(", ", $params) . ")";

        $this->where .= "{$col} IN {$params} ";
        
        return $this;
    }

    public function whereNotIn(string $col, array $values): Model
    {
        if (!$this->where) {
            $this->where = "WHERE ";
        } else {
            $this->where .= "AND ";
        }

        $params = [];

        foreach ($values as $key => $value) {
            $this->addParam($col . ($key + 1), $value);
            $params[] = ':' . $col . ($key + 1);
        }

        $params = "(" . implode(", ", $params) . ")";

        $this->where .= "{$col} NOT IN {$params} ";
        
        return $this;
    }

    public function whereOr(...$statements): Model
    {
        if ($this->where === '') {
            $this->where = "WHERE ";
        } else {
            $this->where .= "OR ";
        }

        if (sizeof($statements) === 2) {
            [$col, $value] = $statements;
            $exp = '=';
            $this->addParam($col, $value);
        }

        if (sizeof($statements) === 3) {
            [$col, $exp, $value] = $statements;
            $this->addParam($col, $value);
        }

        $this->where .= "{$col} {$exp} :{$col} ";
        return $this;
    }

    public function where(...$statements): Model
    {
        if ($this->where === '') {
            $this->where = "WHERE ";
        } else {
            $this->where .= "AND ";
        }

        if (sizeof($statements) === 2) {
            [$col, $value] = $statements;
            $exp = '=';
            $this->addParam($col, $value);
        }

        if (sizeof($statements) === 3) {
            [$col, $exp, $value] = $statements;
            $this->addParam($col, $value);
        }

        $this->where .= "{$col} {$exp} :{$col} ";
        return $this;
    }
}
