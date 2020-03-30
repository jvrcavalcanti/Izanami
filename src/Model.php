<?php

// declare(strict_types = 1);

namespace Accolon\DataLayer;

use PDOException;
use Accolon\DataLayer\Db;

abstract class Model
{
    private $limit;
    private $columns;
    private $offset;
    private $order;
    private $statement;
    private $params;
    private $operation;
    private $where;

    public function select(array $cols = ["*"])
    {
        $this->operation = "select";

        $this->columns = "";

        $cols = is_array($cols) ? $cols : func_get_args();

        $this->columns = implode(", ", $cols);

        $this->statement = "SELECT {$this->columns} FROM {$this->table} ";

        return $this;
    }

    public function find(int $id)
    {
        $this->select();
        
        $this->where .= "WHERE id={$id}";

        return $this->execute();
    }

    public function where(array $where)
    {
        $this->where = "WHERE ";

        // Verifica se é multidimensional, se sim retorna 1 ou maior
        $multi = array_sum(array_map("is_array", $where));
        
        if($multi == 0){
            foreach($where as $key => $value){
                if($key == 0){
                    $value = "`{$value}`";
                }
                if($key == 2){
                    $this->params[] = $value;
                    $value = "?";
                }
                $this->where .= $value . " ";
            }
            return $this;
        }

        if($multi > 0) {
            foreach($where as $key => $value){
                foreach($value as $id => $ele){
                    if($id == 0){
                        $value = "`{$ele}`";
                    }
                    if($id == 2){
                        $this->params[] = $ele;
                        $ele = "?";
                    }
                    $this->where .= $ele . " ";
                }
                if(count($where) - 1 != $key){
                    $this->where .= "AND ";
                }
            }
        }
        
        return $this;
    }

    public function get(bool $object = false)
    {
        $this->operation = "select";

        if(!$this->columns) {
            $this->columns = "* ";
        }

        $this->statement = "SELECT {$this->columns} FROM {$this->table} ";

        return $this->execute(!$object);
    }

    public function delete()
    {
        $this->operation = "delete";
        $this->statement = "DELETE FROM {$this->table} ";
        return $this->execute();
    }

    public function save(): bool
    {
        $exceptions = ["table", "limit", "columns", "statement", "params", "operation", "where", "offset", "order"];
        $this->operation = "insert";

        $fields = [];
        $values = [];

        foreach($this as $key => $value) {
            if(!in_array($key, $exceptions)){
                $fields[] = "`{$key}`";
                $values[] = "'{$value}'";
            }
        }

        $fields = "(" . implode(", ", $fields) . ")";
        $values = "(" . implode(", ", $values) . ")";

        $this->statement = "INSERT INTO {$this->table} {$fields} VALUES {$values}";

        return $this->execute();    
    }

    public function update(array $cols, array $datas)
    {
        $this->operation = "update";

        if(count($cols) != count($datas)){
            return $this;
        }

        $set = "";

        foreach($cols as $key  =>$col){
            $tmp = "`{$col}` = '{$datas[$key]}', ";
            if($key == count($cols) - 1){
                $tmp = "`{$col}` = '{$datas[$key]}' ";
            }
            $set .= $tmp;
        }

        $this->statement = "UPDATE {$this->table} SET {$set}";

        return $this->execute();
    }

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

    public function execute(bool $all = true)
    {
        try{
            $stmt = Db::connection()->prepare($this->statement . $this->where . $this->order . $this->limit . $this->offset);
            $result = $stmt->execute($this->params);

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