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

    private ?string $limit = "";
    private ?string $columns = "";
    private ?string $offset = "";
    private ?string $order = "";
    private ?string $statement = "";
    private ?array $params = [];
    private ?int $operation = 0;
    private ?string $where = "";

    public function __construct(object $obj = null)
    {
        $this->persist($obj);
    }

    public function persist(?object $obj): void
    {
        if (!$obj) {
            return;
        }

        foreach($obj as $attr => $value) {
            $this->$attr = $value;
        }
    }

    public function clear()
    {
        $attrs = ["limit", "offset", "columns", "order", "statement", "params", "operation", "where"];
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

    public function execute(bool $all = true)
    {
        try{
            $db = Db::connection();

            $db->beginTransaction();

            $stmt = $db->prepare(
                $this->statement . $this->where . $this->order . $this->limit . $this->offset
            );

            $result = $stmt->execute($this->params);

            $db->commit();

            if(!$stmt->rowCount()){
                return null;
            }

            if($this->operation == Operation::Count) {
                return $stmt->rowCount();
            }

            if ($this->operation == Operation::Insert) {
                $this->id = $db->lastInsertId();
            }

            if($this->operation != Operation::Select){
                return $result;
            }

            $this->clear();

            if($all){
                return $stmt->fetchAll();
            }
            
            $result = $stmt->fetchObject();

            $obj = new $this($result);

            $obj->persist($result);

            $obj->table = $this->table;

            return $obj;

        }catch(PDOException $e){
            // echo $this->statement . $this->where . $this->order . $this->limit . $this->offset; // Debug
            $db->rollBack();
            die("Error: {$e->getMessage()}");
        }
    }
}