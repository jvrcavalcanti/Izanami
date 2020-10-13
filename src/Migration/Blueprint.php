<?php

namespace Accolon\Izanami\Migration;

class Blueprint
{
    private $fields = [];

    public function addField(Field $field): Field
    {
        $this->fields[] = $field;
        return $this->fields[sizeof($this->fields) - 1];
    }

    public function increments($name)
    {
        return $this->integer($name)->increments()->primaryKey();
    }

    public function bigIncrements($name)
    {
        return $this->bigInteger($name)->increments()->primaryKey();
    }

    public function id()
    {
        return $this->bigIncrements("id");
    }

    public function string($name, $length = 255)
    {
        return $this->addField(Field::create($name, "VARCHAR({$length})"));
    }

    public function char($name)
    {
        return $this->addField(Field::create($name, "CHAR"));
    }

    public function binary($name)
    {
        return $this->addField(Field::create($name, "BINARY"));
    }

    public function text($name)
    {
        return $this->addField(Field::create($name, "TEXT"));
    }

    public function mediumText($name)
    {
        return $this->addField(Field::create($name, "MEDIUMTEXT"));
    }

    public function longText($name)
    {
        return $this->addField(Field::create($name, "LONGTEXT"));
    }

    public function enum($name, array $values)
    {
        $string = implode(", ", array_map(fn($value)=> "'" . $value . "'", $values));
        return $this->addField(Field::create($name, "ENUM({$string})"));
    }

    public function set($name, array $values)
    {
        $string = implode(", ", array_map(fn($value)=> "'" . $value . "'", $values));
        return $this->addField(Field::create($name, "SET({$string})"));
    }

    public function bit($name)
    {
        return $this->addField(Field::create($name, "BIT"));
    }

    public function tinyInt($name)
    {
        return $this->addField(Field::create($name, "TINYINT"));
    }

    public function integer($name)
    {
        return $this->addField(Field::create($name, "INT"));
    }

    public function bigInteger($name)
    {
        return $this->addField(Field::create($name, "BIGINT"));
    }

    public function boolean($name)
    {
        return $this->addField(Field::create($name, "BOOL"));
    }

    public function json($name)
    {
        return $this->addField(Field::create($name, "JSON"));
    }

    public function date($name)
    {
        return $this->addField(Field::create($name, "DATE"));
    }

    public function dateTime($name)
    {
        return $this->addField(Field::create($name, "DATETIME"));
    }

    public function year($name)
    {
        return $this->addField(Field::create($name, "YEAR"));
    }

    public function timestamp($name)
    {
        return $this->addField(Field::create($name, "TIMESTAMP"));
    }

    public function timestamps()
    {
        $this->timestamp("created_at")->default("CURRENT_TIMESTAMP");
        $this->timestamp("updated_at")->default("CURRENT_TIMESTAMP")->onUpdate("CURRENT_TIMESTAMP");
    }

    public function time($name)
    {
        return $this->addField(Field::create($name, "TIME"));
    }

    public function double($name, $size1, $size2)
    {
        return $this->addField(Field::create($name, "DOUBLE({$size1}, {$size2})"));
    }

    public function float($name, $size1, $size2)
    {
        return $this->addField(Field::create($name, "FLOAT({$size1}, {$size2})"));
    }

    public function getFields(): string
    {
        $fields = array_map(fn($field) => (string) $field, $this->fields);
        return implode(", ", $this->fields);
    }
}
