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

        $result = $this->execute(false);

        return $result ? static::build($this->table, $result) : null;
    }

    public function exists(): bool
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
        return $this->selectConfig()->where("id", $id)->get();
    }

    public function find(string $field, string $value)
    {
        return $this->selectConfig()->where($field, $value)->get();
    }

    public function all(): array
    {
        $this->selectConfig();

        return $this->getAll();
    }

    public function where($statements): Model
    {
        $this->where = "WHERE ";

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