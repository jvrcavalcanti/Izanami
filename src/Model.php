<?php

declare(strict_types = 1);

namespace Accolon\DataLayer;

use Accolon\DataLayer\DB;
use ReflectionClass;
use Accolon\DataLayer\Exceptions\FailQueryException;
use JsonSerializable;

abstract class Model implements JsonSerializable
{
    private $limit;
    private $columns;
    private $offset;
    private $order;
    private $statement;
    private $params = [];
    private $operation = 0;
    private $where;
    private $attributes = [];
    private $exist = false;

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;

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
            $this->attributes,
            fn($attr) => !in_array($attr, $sensitives),
            ARRAY_FILTER_USE_KEY
        );
    }

    public function getAttributes(): array
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

    public function setExist($value): Model
    {
        $this->exist = $value;
        return $this;
    }

    public function count(): int
    {
        $this->select();

        $this->operation = Operation::Count;

        return $this->execute();
    }

    public function addSelect(string $col): Model
    {
        $this->columns .= ", {$col}";

        $this->statement = "SELECT {$this->columns} FROM {$this->table} ";

        return $this;
    }

    public static function build(string $table, $data): Model
    {
        $refletor = new ReflectionClass(static::class);

        $obj = $refletor->newInstance();

        $obj->setTable($table);

        $obj->persist($data);

        return $obj;
    }

    public function getStatement()
    {
        return $this->statement . $this->where . $this->order . $this->limit . $this->offset;
    }

    public function execute(bool $all = true)
    {
        $db = DB::connection();

        $stmt = $db->prepare(
            $this->statement . $this->where . $this->order . $this->limit . $this->offset
        );

        $result = $stmt->execute($this->params);

        switch ($this->operation) {
            case Operation::Count:
                $this->clear();
                return $stmt->rowCount();

            case Operation::Select:
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

            case Operation::Insert:
                $this->id = $db->lastInsertId();

            default:
                $this->clear();
                return $result;
        }
    }

    /* ********************* CRUD *********************** */

    public function select(array $cols = ["*"]): Model
    {
        $this->operation = Operation::Select;

        $this->columns = "";

        $cols = is_array($cols) ? $cols : func_get_args();

        $this->columns = implode(", ", $cols);

        $this->statement = "SELECT {$this->columns} FROM {$this->table} ";

        return $this;
    }

    public function delete(): bool
    {
        $this->operation = Operation::Delete;
        $this->statement = "DELETE FROM {$this->table} ";

        if ($this->exist) {
            $where = $this->transformData($this->attributes);
            $this->where($where);
        }

        return $this->execute();
    }

    public function create(array $data): bool
    {
        $this->operation = Operation::Insert;

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
            $this->setExist(true);
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

        if ($this->exist) {
            $where = $this->transformData($data);
            return $this->where($where)->update($data);
        }

        return $this->create($data);
    }

    public function update(array $cols)
    {
        $this->operation = Operation::Update;

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

    public function getAll($columns = ["*"]): array
    {
        $this->query()->select($columns);

        $result = $this->execute(true);

        if (!$result) {
            return [];
        }

        return array_map(
            fn($obj) => static::build($this->table, $obj)->setExist(true),
            $result
        );
    }

    public function get($columns = ["*"])
    {
        $this->query()->select($columns);

        $result = $this->execute(false);

        return $result ? static::build($this->table, $result)->setExist(true) : null;
    }

    public function first($columns = ["*"])
    {
        $this->query()->select($columns);

        $result = $this->getAll();

        return (sizeof($result) === 0) ? null : $result[0];
    }

    public function firstWhere(string ...$params)
    {
        return $this->query()->where($params)->first();
    }

    public function exists(): bool
    {
        $result = $this->query()->get();

        $this->exist = $result ? true : false;

        return $this->exist;
    }

    public function query(): Model
    {
        $this->operation = Operation::Select;

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

    public function findById(int $id)
    {
        return $this->query()->where("id", $id)->get();
    }

    public function find(string $field, string $value)
    {
        return $this->query()->where($field, $value)->get();
    }

    public function findOrFail(string $field, string $value)
    {
        $result = $this->find($field, $value);

        if (!$result) {
            throw new FailQueryException("Find failed");
        }

        return $result;
    }

    public function all(): array
    {
        $this->query();

        return $this->getAll();
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
                $this->where .= "{$this->table}.{$statements[0]} = ?";
            }

            if (sizeof($statements) == 3) {
                $this->addParam($statements[2]);
                $this->where .= "{$this->table}.{$statements[0]} {$statements[1]} ?";
            }
            return $this;
        }

        if ($multi > 0) {
            foreach ($statements as $key => $value) {
                if (sizeof($value) == 2) {
                    $this->addParam($value[1]);
                    $this->where .= "{$this->table}.{$value[0]} = ? ";
                }
                
                if (sizeof($value) == 3) {
                    $this->addParam($value[2]);
                    $this->where .= "{$this->table}.{$value[0]} {$value[1]} ? ";
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
                $this->where .= "{$this->table}.{$statements[0]} = ?";
            }

            if (sizeof($statements) == 3) {
                $this->addParam($statements[2]);
                $this->where .= "{$this->table}.{$statements[0]} {$statements[1]} ?";
            }
            return $this;
        }

        if ($multi > 0) {
            foreach ($statements as $key => $value) {
                if (sizeof($value) == 2) {
                    $this->addParam($value[1]);
                    $this->where .= "{$this->table}.{$value[0]} = ? ";
                }
                
                if (sizeof($value) == 3) {
                    $this->addParam($value[2]);
                    $this->where .= "{$this->table}.{$value[0]} {$value[1]} ? ";
                }

                if (count($statements) - 1 != $key) {
                    $this->where .= "AND ";
                }
            }
        }
        
        return $this;
    }
}
