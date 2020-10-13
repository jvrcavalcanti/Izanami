<?php

namespace Accolon\Izanami\Migration;

class Field
{
    private string $name;
    private string $type;
    
    private ?string $default = null;
    private ?string $onUpdate = null;
    private ?string $onDelete = null;

    private bool $nullable = false;
    private bool $unique = false;
    private bool $primaryKey = false;
    private bool $increments = false;

    public function __construct($name, $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public static function create(string $name, string $type)
    {
        return new Field($name, $type);
    }

    public function nullable(): Field
    {
        $this->nullable = true;
        return $this;
    }

    public function unique(): Field
    {
        $this->unique = true;
        return $this;
    }

    public function increments(): Field
    {
        $this->increments = true;
        return $this;
    }

    public function primaryKey(): Field
    {
        $this->primaryKey = true;
        return $this;
    }

    public function default($value): Field
    {
        $this->default = $value;
        return $this;
    }

    public function onUpdate($value): Field
    {
        $this->onUpdate = $value;
        return $this;
    }

    public function onDelete($value): Field
    {
        $this->onDelete = $value;
        return $this;
    }

    public function __toString(): string
    {
        $string = "";

        $string .= "`{$this->name}` {$this->type}";

        if (!$this->nullable) {
            $string .= " NOT NULL";
        }

        if ($this->increments) {
            $string .= " AUTO_INCREMENT";
        }

        if ($this->unique) {
            $string .= " UNIQUE";
        }

        if ($this->primaryKey) {
            $string .= " PRIMARY KEY";
        }

        if ($this->default) {
            $string .= " DEFAULT {$this->default}";
        }

        if ($this->onUpdate) {
            $string .= " ON UPDATE {$this->onUpdate}";
        }

        if ($this->onDelete) {
            $string .= " ON DELETE {$this->onUpdate}";
        }

        return $string;
    }
}
