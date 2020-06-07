<?php

declare(strict_types = 1);

namespace Accolon\DataLayer;

use PDOException;
use Accolon\DataLayer\Db;
use Accolon\DataLayer\Traits\CRUD;
use Accolon\DataLayer\Traits\Query;
use Closure;
use ReflectionClass;

abstract class Model
{
    use Query, CRUD;

    private $limit = "";
    private $columns = "";
    private $offset = "";
    private $order = "";
    private $statement = "";
    private $params = [];
    private $operation = 0;
    private $where = "";
    private $attributes = [];

    public function __construct(string $table = "")
    {
        if (!isset($this->table)) {
            $this->table = $table;
        }
    }

    public static function attributesModel()
    {
        return [
            "limit",
            "offset",
            "columns",
            "order",
            "statement",
            "params",
            "operation",
            "where",
            "attributes"
        ];
    }

    public function persist($iterable): void
    {
        if (!is_array($iterable) && !is_object($iterable)) {
            throw new \Exception("Not's iterable");
        }

        foreach($iterable as $attr => $value) {
            $this->$attr = $value;
        }
    }

    public function when(bool $option, Closure $action): Model
    {
        if ($option) {
            $action($this);
        }

        return $this;
    }

    public function clear()
    {
        $attrs = self::attributesModel();
        foreach($attrs as $attr) {
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

    public function getStatement()
    {
        return $this->statement . $this->where . $this->order . $this->limit . $this->offset;
    }

    public function execute(bool $all = true)
    {
        $db = Db::connection();

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
}