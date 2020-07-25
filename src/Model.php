<?php

declare(strict_types = 1);

namespace Accolon\DataLayer;

use Accolon\DataLayer\DB;
use ReflectionClass;
use Accolon\DataLayer\Exceptions\FailQueryException;
use JsonSerializable;
use Accolon\DataLayer\Interfaces\Jsonable;
use Accolon\DataLayer\Interfaces\Arrayable;

abstract class Model implements JsonSerializable, Jsonable, Arrayable
{
    private $joinS;
    private $limit;
    private $columns;
    private $offset;
    private $order;
    private $statement;
    private $params = [];
    private $operation = 0;
    private $where;
    private $attributes = [];
    private $exists = false;

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
    }

    public function setTable(string $table): Model
    {
        $this->table = $table;
        return $this;
    }

    public function __get($name)
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        return null;
    }

    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
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

    public static function attributesModel()
    {
        $refletor = new \ReflectionClass(self::class);
        return [
            ...array_map(fn($prop) => $prop->getName(), $refletor->getProperties()),
            "table",
            "sensitives"
        ];
    }

    public function persist($iterable): void
    {
        if (!is_array($iterable) && !is_object($iterable)) {
            throw new \Exception("Not's iterable");
        }

        foreach ($iterable as $attr => $value) {
            $this->attributes[$attr] = $value;
        }
    }

    public function clear()
    {
        $attrs = self::attributesModel();
        foreach ($attrs as $attr) {
            if ($attr === "table" || $attr === "sensitives" || $attr === "attributes") {
                continue;
            }
            $this->$attr = null;
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

        $this->operation = Operation::COUNT;

        return $this->execute();
    }

    public function addSelect(string $col): Model
    {
        $this->columns .= ", {$col}";

        $this->statement = "SELECT {$this->columns} FROM {$this->table} ";

        return $this;
    }

    public static function build($data): Model
    {
        $refletor = new ReflectionClass(static::class);

        $obj = $refletor->newInstance();

        $obj->persist($data);

        return $obj;
    }

    public static function builder()
    {
        return new Builder(static::class);
    }

    public function getStatement()
    {
        return $this->statement . $this->where . $this->order . $this->limit . $this->offset;
    }

    private function execute(bool $all = true)
    {
        $db = DB::connection();

        $stmt = $db->prepare(
            $this->statement . $this->joinS . $this->where . $this->order . $this->limit . $this->offset
        );

        $result = $stmt->execute($this->params);

        switch ($this->operation) {
            case Operation::COUNT:
                $this->clear();
                return $stmt->rowCount();

            case Operation::SELECT:
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

            case Operation::INSERT:
                $this->id = $db->lastInsertId();

            default:
                $this->clear();
                return $result;
        }
    }

    /* ********************* CRUD *********************** */

    public function select(array $cols = ["*"]): Model
    {
        $this->operation = Operation::SELECT;

        $this->columns = "";

        $cols = is_array($cols) ? $cols : func_get_args();

        $this->columns = implode(", ", $cols);

        $this->statement = "SELECT {$this->columns} FROM {$this->table} ";

        return $this;
    }

    public function delete(): bool
    {
        $this->operation = Operation::DELETE;
        $this->statement = "DELETE FROM {$this->table} ";

        if ($this->exists) {
            $where = $this->transformData($this->attributes);
            $this->where($where);
        }

        return $this->execute();
    }

    public function create(array $data): bool
    {
        $this->operation = Operation::INSERT;

        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            $fields[] = "`{$key}`";
            $values[] = "'{$value}'";
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

    public function transformData(array $data)
    {
        $where = [];
        foreach ($data as $key => $value) {
            $where[] = [$key, $value];
        }
        return $where;
    }

    public function save(): bool
    {
        $data = $this->attributes;

        if ($this->exists) {
            $where = $this->transformData($data);
            return $this->where($where)->update($data);
        }

        return $this->create($data);
    }

    public function update(array $cols)
    {
        $this->operation = Operation::UPDATE;

        $set = "";

        $i = 0;

        foreach ($cols as $key => $col) {
            $tmp = "`{$key}` = '{$col}', ";
            if ($i == count($cols) - 1) {
                $tmp = "`{$key}` = '{$col}' ";
            }
            $set .= $tmp;
            $i++;
        }

        $this->statement = "UPDATE {$this->table} SET {$set}";

        return $this->execute();
    }

    /* ****************************** Query ************************** */

    public function getAll($columns = ["*"]): Collection
    {
        $this->query()->select($columns);
            
        $result = $this->execute(true);

        if (!$result) {
            return new Collection([]);
        }

        return new Collection(array_map(
            fn($obj) => static::build($obj)->setExists(true),
            $result
        ));
    }

    public function get($columns = ["*"])
    {
        $this->query()->select($columns);

        $result = $this->execute(false);

        return $result ? static::build($result)->setExists(true) : null;
    }

    public function first($columns = ["*"])
    {
        $this->query()->select($columns);

        $result = $this->getAll();

        return (sizeof($result) === 0) ? null : $result[0];
    }

    private function fail($result)
    {
        if (!$result) {
            throw new FailQueryException("Find by Id failed");
        }
    }

    public function firstWhere(string ...$params)
    {
        return $this->query()->where($params)->first();
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

        $this->exists = $result ? true : false;

        return $this->exists;
    }

    public function query(): Model
    {
        $this->operation = Operation::SELECT;

        if (!$this->columns || $this->columns == "") {
            $this->columns = "*";
        }

        $this->statement = "SELECT {$this->columns} FROM {$this->table} ";

        return $this;
    }

    public function addParam($param)
    {
        $this->params[] = $param;
    }

    public function addParams(array $params)
    {
        $this->params = [...$this->params, ...$params];
    }

    public function getParams(): array
    {
        return $this->params ?? [];
    }

    public function findId(string $id)
    {
        return $this->query()->where("id", $id)->get();
    }

    public function findIdOrFail(string $id)
    {
        $result = $this->findId($id);

        $this->fail($result);

        return $result;
    }

    public function find(string $field, string $value)
    {
        return $this->query()->where($field, $value)->get();
    }

    public function findOrFail(string $field, string $value)
    {
        $result = $this->find($field, $value);

        $this->fail($result);

        return $result;
    }

    public function all($columns = ["*"])
    {
        $this->query();

        return $this->getAll($columns);
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

        $params = "(" . implode(", ", array_map(fn($value) => "?", $values)) . ")";
        $this->addParams($values);

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

        $params = "(" . implode(", ", array_map(fn($value) => "?", $values)) . ")";
        $this->addParams($values);

        $this->where .= "{$col} NOT IN {$params} ";
        
        return $this;
    }

    public function whereOr($statements): Model
    {
        if (!$this->where) {
            $this->where = "WHERE ";
        } else {
            $this->where .= "OR ";
        }

        if (!is_array($statements)) {
            $statements = func_get_args();
        }

        // Verifica se é multidimensional, se sim retorna 1 ou maior
        $multi = array_sum(array_map("is_array", $statements));
        
        if ($multi == 0) {
            if (sizeof($statements) == 2) {
                $this->addParam($statements[1]);
                $this->where .= "{$statements[0]} = ?";
            }

            if (sizeof($statements) == 3) {
                $this->addParam($statements[2]);
                $this->where .= "{$statements[0]} {$statements[1]} ?";
            }
            return $this;
        }

        if ($multi > 0) {
            foreach ($statements as $key => $value) {
                if (sizeof($value) == 2) {
                    $this->addParam($value[1]);
                    $this->where .= "{$value[0]} = ? ";
                }
                
                if (sizeof($value) == 3) {
                    $this->addParam($value[2]);
                    $this->where .= "{$value[0]} {$value[1]} ? ";
                }

                if (count($statements) - 1 != $key) {
                    $this->where .= "OR ";
                }
            }
        }
        
        return $this;
    }

    public function where($statements): Model
    {
        if (!$this->where) {
            $this->where = "WHERE ";
        } else {
            $this->where .= " AND ";
        }

        if (!is_array($statements)) {
            $statements = func_get_args();
        }

        // Verifica se é multidimensional, se sim retorna 1 ou maior
        $multi = array_sum(array_map("is_array", $statements));
        
        if ($multi == 0) {
            if (sizeof($statements) == 2) {
                $this->addParam($statements[1]);
                $this->where .= "{$statements[0]} = ?";
            }

            if (sizeof($statements) == 3) {
                $this->addParam($statements[2]);
                $this->where .= "{$statements[0]} {$statements[1]} ?";
            }
            return $this;
        }

        if ($multi > 0) {
            foreach ($statements as $key => $value) {
                if (sizeof($value) == 2) {
                    $this->addParam($value[1]);
                    $this->where .= "{$value[0]} = ? ";
                }
                
                if (sizeof($value) == 3) {
                    $this->addParam($value[2]);
                    $this->where .= "{$value[0]} {$value[1]} ? ";
                }

                if (count($statements) - 1 != $key) {
                    $this->where .= "AND ";
                }
            }
        }
        
        return $this;
    }
}
