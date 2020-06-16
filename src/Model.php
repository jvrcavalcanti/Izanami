<?php

declare(strict_types = 1);

namespace Accolon\DataLayer;

use Accolon\DataLayer\DB;
use ReflectionClass;
use Accolon\DataLayer\Exceptions\FailQueryException;

abstract class Model
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

    public function __construct(string $table = "")
    {
        if (!isset($this->table)) {
            $this->table = $table;
        }
    }

    public function __get($name)
    {
        return $this->attributes[$name];
    }

    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }

    public static function attributesModel()
    {
        $refletor = new \ReflectionClass(self::class);
        return [
            ...array_map(fn($prop) => $prop->getName(), $refletor->getProperties()),
            "table",
            "safes"
        ];
    }

    public function persist($iterable): void
    {
        if (!is_array($iterable) && !is_object($iterable)) {
            throw new \Exception("Not's iterable");
        }

        foreach($iterable as $attr => $value) {
            $this->attributes[$attr] = $value;
        }
    }

    public function clear()
    {
        $attrs = self::attributesModel();
        foreach($attrs as $attr) {
            if ($attr === "table" || $attr === "safes" || $attr === "attributes") {
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

        $obj = $refletor->newInstance($table);

        $obj->persist($data);

        return $obj;
    }

    private function filter(): Model
    {
        $safes = isset($this->safe) ? $this->safe : [];
        $exceptions = self::attributesModel();

        foreach ($this as $attr => $value) {
            if (!in_array($attr, $exceptions) && !in_array($attr, $safes)) {
                unset($this->$attr);
            }
        }

        return $this;
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

        switch($this->operation) {
            case Operation::Count:
                $this->clear();
                return $stmt->rowCount();

            case Operation::Select:
                if(!$stmt->rowCount()){
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
        return $this->execute();
    }

    public function create(array $data): bool
    {
        $this->operation = Operation::Insert;

        $fields = [];
        $values = [];

        foreach($data as $key => $value) {
            $fields[] = "`{$key}`";
            $values[] = "'{$value}'";
        }

        $fields = "(" . implode(", ", $fields) . ")";
        $values = "(" . implode(", ", $values) . ")";

        $this->statement = "INSERT INTO {$this->table} {$fields} VALUES {$values}";

        $result = $this->execute();

        if ($result) {
            $this->persist($data);
        }

        return $result;
    }

    public function save(): bool
    {
        $exceptions = self::attributesModel();

        $data = $this->attributes;

        if ($this->exist) {
            $where = [];
            foreach ($data as $key => $value) {
                $where[] = [$key, $value];
            }
            return $this->where($where)->update($data);
        }

        return $this->create($data);    
    }

    public function update(array $cols)
    {
        $this->operation = Operation::Update;

        $set = "";

        $i = 0;

        foreach($cols as $key => $col){
            $tmp = "`{$key}` = '{$col}', ";
            if($i == count($cols) - 1){
                $tmp = "`{$key}` = '{$col}' ";
            }
            $set .= $tmp;
            $i++;
        }

        $this->statement = "UPDATE {$this->table} SET {$set}";

        return $this->execute();
    }

    /* ****************************** Query ************************** */

    public function getAll(): array
    {
        $this->query();

        $result = $this->execute(true);

        if (!$result) {
            return [];
        }

        return array_map(
            fn($obj) => static::build($this->table, $obj)->setExist(true)->filter(),
            $result
        );
    }

    public function get()
    {
        $this->query();

        $result = $this->execute(false);

        return $result ? static::build($this->table, $result)->setExist(true)->filter() : null;
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

        if(!$this->columns || $this->columns == "") {
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
        
        if($multi == 0){
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

        if($multi > 0) {
            foreach($statements as $key => $value){
                if (sizeof($value) == 2) {
                    $this->addParam($value[1]);
                    $this->where .= "{$this->table}.{$value[0]} = ? ";
                }
                
                if (sizeof($value) == 3) {
                    $this->addParam($value[2]);
                    $this->where .= "{$this->table}.{$value[0]} {$value[1]} ? ";
                }

                if(count($statements) - 1 != $key){
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
        
        if($multi == 0){
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

        if($multi > 0) {
            foreach($statements as $key => $value){
                if (sizeof($value) == 2) {
                    $this->addParam($value[1]);
                    $this->where .= "{$this->table}.{$value[0]} = ? ";
                }
                
                if (sizeof($value) == 3) {
                    $this->addParam($value[2]);
                    $this->where .= "{$this->table}.{$value[0]} {$value[1]} ? ";
                }

                if(count($statements) - 1 != $key){
                    $this->where .= "AND ";
                }
            }
        }
        
        return $this;
    }
}