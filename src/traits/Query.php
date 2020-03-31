<?php

namespace Accolon\DataLayer\Traits;

trait Query
{
    public function get(bool $object = false)
    {
        $this->selectConfig();

        $result = $this->execute(!$object);

        // return $result && sizeof($result) == 1 ? $result[0] : $result;
        return $result;
    }

    public function selectConfig()
    {
        $this->operation = "select";

        if(!$this->columns) {
            $this->columns = "* ";
        }

        $this->statement = "SELECT {$this->columns} FROM {$this->table} ";
    }

    public function find(int $id)
    {
        $this->selectConfig();
        
        $this->where .= "WHERE id={$id}";

        return $this->execute();
    }

    public function all()
    {
        $this->selectConfig();

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
}