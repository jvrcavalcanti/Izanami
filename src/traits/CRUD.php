<?php

namespace Accolon\DataLayer\Traits;

trait CRUD
{
    public function select(array $cols = ["*"])
    {
        $this->operation = "select";

        $this->columns = "";

        $cols = is_array($cols) ? $cols : func_get_args();

        $this->columns = implode(", ", $cols);

        $this->statement = "SELECT {$this->columns} FROM {$this->table} ";

        return $this;
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

    public function update(array $cols)
    {
        $this->operation = "update";

        $set = "";

        foreach($cols as $key => $col){
            $tmp = "`{$key}` = '{$col}', ";
            if($key == count($cols) - 1){
                $tmp = "`{$key}` = '{$col}' ";
            }
            $set .= $tmp;
        }

        $this->statement = "UPDATE {$this->table} SET {$set}";

        return $this->execute();
    }
}