<?php

namespace App;

use App\Model;

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