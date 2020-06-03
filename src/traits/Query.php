<?php

namespace Accolon\DataLayer\Traits;

use Accolon\DataLayer\Model;
use Accolon\DataLayer\Operation;

trait Query
{
    public function getAll(): array
    {
        $this->selectConfig();

        $result = $this->execute(true);

        if (!$result) {
            return [];
        }

        return array_map(fn($obj) => static::build($this->table, $obj), $result);
    }

    public function get()
    {
        $this->selectConfig();

        // return $result && sizeof($result) == 1 ? $result[0] : $result;
        $result = $this->execute(false);

        return $result ? static::build($this->table, $result) : null;
    }

    public function exist(): bool
    {
        $result = $this->selectConfig()->get();

        return $result ? true : false;
    }

    public function selectConfig(): Model
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
        return $this->selectConfig()->where(["id", "=", $id])->get();
    }

    public function find(string $field, string $value)
    {
        return $this->selectConfig()->where([$field, "=", $value])->get();
    }

    public function all(): array
    {
        $this->selectConfig();

        return $this->getAll();
    }

    public function where(array $where): Model
    {
        $this->where = "WHERE ";

        // Verifica se é multidimensional, se sim retorna 1 ou maior
        $multi = array_sum(array_map("is_array", $where));
        
        if($multi == 0){
            foreach($where as $key => $value){
                if($key == 0){
                    $value = "{$this->table}.{$value}";
                }
                if($key == 2){
                    $this->addParam($value);
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
                        $value = "{$this->table}.{$ele}";
                    }
                    if($id == 2){
                        $this->addParam($ele);
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
}