<?php

namespace App;

use Accolon\DataLayer\Model;

class Db
{
    public static function table(string $table)
    {
        return new class($table) extends Model {
            protected $table;

            public function __construct($table)
            {
                $this->table = $table;
            }
        };
    }
}