<?php

namespace Test;

use Accolon\Izanami\Model;
use Accolon\Izanami\DB;

class Test extends Model
{
    protected string $table = "test";

    protected $sensitives = ["password"];

    public function posts()
    {
        return DB::table('posts')
                    ->where("user_id", $this->id)
                    ->getAll();
    }
}
