<?php

namespace Accolon\DataLayer\Traits;

use Accolon\DataLayer\Model;
use Accolon\DataLayer\Operation;

trait Query
{
    public function getAll()
    {
        $this->selectConfig();

        return $this->execute(true);
    }

    public function get()
    {
        $this->selectConfig();

        // return $result && sizeof($result) == 1 ? $result[0] : $result;
        $result = $this->execute(false);

        return $result;
    }

    public function selectConfig()
    {
        $this->operation = Operation::Select;

        if(!$this->columns) {
            $this->columns = "*";
        }

        $this->statement = "SELECT {$this->columns} FROM {$this->table} ";
    }

    public function find(int $id): object
    {
        $this->selectConfig();
        
        $this->where .= "WHERE id= ?";

        $this->params[] = $id;

        return $this->get();
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
                        $value = "{$this->table}.{$ele}";
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
}