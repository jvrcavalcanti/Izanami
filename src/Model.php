<?php

declare(strict_types = 1);

namespace Accolon\DataLayer;

use PDOException;
use Accolon\DataLayer\Db;
use Accolon\DataLayer\Traits\CRUD;
use Accolon\DataLayer\Traits\Query;

abstract class Model
{
    use Query, CRUD;

    private string $limit = "";
    private string $columns = "";
    private string $offset = "";
    private string $order = "";
    private string $statement = "";
    private array $params = [];
    private int $operation = 0;
    private string $where = "";

    public function persist($iterable): void
    {
        foreach($iterable as $attr => $value) {
            $this->$attr = $value;
        }
    }

    public function clear()
    {
        $attrs = ["limit", "offset", "columns", "order", "statement", "params", "operation", "where"];
        foreach($attrs as $attr) {
            if (is_string($attr)) {
                $this->$attr = "";
            }

            if (is_int($attr)) {
                $this->$attr = 0;
            }

            if (is_array($attr)) {
                $this->$attr = [];
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

                $obj = new $this();

                $obj->persist($result);

                $obj->table = $this->table;

                $this->clear();

                return $obj;

            case Operation::Insert:
                $this->id = $db->lastInsertId();

            default:
                $this->clear();
                return $result;

        }
    }
}