<?php

// declare(strict_types = 1);

namespace Accolon\DataLayer;

use PDOException;
use Accolon\DataLayer\Db;
use Accolon\DataLayer\Traits\CRUD;
use Accolon\DataLayer\Traits\Query;

abstract class Model
{
    use Query, CRUD;

    private $limit;
    private $columns;
    private $offset;
    private $order;
    private $statement;
    private $params;
    private $operation;
    private $where;

    public function limit(int $num)
    {
        $this->limit = "LIMIT {$num} ";
        return $this;
    }

    public function offset(int $num)
    {
        $this->offset = "OFFSET {$num} ";
        return $this;
    }

    public function order(string $col, string $order)
    {
        $this->order = "ORDER BY {$col} {$order} ";
        return $this;
    }

    public function count()
    {
        $this->select();

        $this->operation = "count";

        return $this->execute();
    }

    public function addSelect(string $col)
    {
        $this->columns .= ", {$col}";

        $this->statement = "SELECT {$this->columns} FROM {$this->table} ";

        return $this;
    }

    public function clear()
    {
        foreach($this as $attr) {
            $attr = null;
        }
    }

    public function execute(bool $all = true)
    {
        try{
            $stmt = Db::connection()->prepare($this->statement . $this->where . $this->order . $this->limit . $this->offset);
            $result = $stmt->execute($this->params);

            $this->clear();

            if(!$stmt->rowCount()){
                return null;
            }

            if($this->operation == "count") {
                return $stmt->rowCount();
            }

            if($this->operation != "select"){
                return $result;
            }

            if($all){
                return $stmt->fetchAll();
            }
            
            return $stmt->fetchObject();

        }catch(PDOException $e){
            die("Error: {$e->getMessage()}");
        }
    }
}