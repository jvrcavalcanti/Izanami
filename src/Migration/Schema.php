<?php

namespace Accolon\Izanami\Migration;

use Accolon\Izanami\Migration\Blueprint;
use Accolon\Izanami\DB;
use Exception;

class Schema
{
    public static function create(string $table, callable $action): bool
    {
        $blueprint = new Blueprint();
        $action($blueprint);
        $sql = "CREATE TABLE {$table} (" . $blueprint->getFields() . ")";
        $result = DB::raw($sql);

        if (!$result) {
            throw new Exception("Failure migration");
        }

        return $result;
    }

    public static function drop(string $table): bool
    {
        return DB::raw("DROP TABLE {$table}");
    }

    public static function dropIfExists(string $table): bool
    {
        return DB::raw("DROP TABLE IF EXISTS {$table}");
    }
}