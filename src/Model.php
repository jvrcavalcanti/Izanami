<?php

namespace App;

use App\Connection;
use PDOException;

abstract class Model
{
    private $limit;
    private $offset;
    private $order;
    private $statement;
    private $params;
    private $operation;
    private $count;
    private $where;

    public function __construct()
    {
        $cols = $this->select()->execute(false);

        foreach($cols as $key => $value){
            $key = strtolower($key);
            $this->$key = null;
        }
    }

    public function select($cols = "*")
    {
        $this->operation = "select";

        $this->statement = "SELECT {$cols} FROM {$this->table} ";

        return $this;
    }

    public function where($where)
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
            return $this->where;
        }

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
        return $this->where;
    }

    public function delete()
    {
        $this->operation = "delete";
        $this->statement = "DELETE FROM {$this->table} ";
    }

    public function save($cols, $datas)
    {
        if(count($cols) != count($datas)){
            return null;
        }

        $fields = "(";
        $values = "(";

        foreach($cols as $key=>$col){
            $field = "`" . $col . "`, ";
            if($key == count($cols) - 1){
                $field = "`" . $col . "`";
            }
            $fields .= $field;

            $value = "'" . $datas[$key] . "', ";
            if($key == count($datas) - 1){
                $value = "'" . $datas[$key] . "'";
            }
            $values .= $value;
            
        }

        $fields .= ")";
        $values .= ")";

        $this->operation = "insert";
        $this->statement = "INSERT INTO {$this->table} {$fields} VALUES {$values}";

        return $this;
    }

    public function update($cols, $datas)
    {
        $this->operation = "update";

        $set = "";

        foreach($cols as $key=>$col){
            $tmp = "`{$col}` = '{$datas[$key]}', ";
            if($key == count($cols) - 1){
                $tmp = "`{$col}` = '{$datas[$key]}' ";
            }
            $set .= $tmp;
        }

        return $this->statement = "UPDATE {$this->table} SET {$set}";
    }

    public function limit($num)
    {
        $this->limit = "LIMIT {$num} ";
    }

    public function offset($num)
    {
        $this->offset = "OFFSET {$num} ";
    }

    public function order($order)
    {
        $this->order = "ORDER BY {$order} ";
    }

    public function execute($all = true)
    {
        try{
            $stmt = Connection::conn()->prepare($this->statement . $this->where . $this->order . $this->limit . $this->offset);
            $result = $stmt->execute($this->params);

            if(!$stmt->rowCount()){
                return null;
            }

            $this->count = $stmt->rowCount();

            if($this->operation != "select"){
                return $result;
            }

            if($all){
                return $stmt->fetchAll();
            }

            $result = $stmt->fetchObject();

            foreach($result as $key => $value){
                $key = strtolower($key);
                $this->$key = $value;
            }
            
            return $stmt->fetchObject();

        }catch(PDOException $e){
            echo "Error: {$e->getMessage()}";
            return null;
        }
    }
}