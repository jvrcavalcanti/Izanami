<?php

namespace Test;

use Accolon\DataLayer\Model;

class Test extends Model
{
    protected string $table = "test";

    protected $sensitives = ["password"];
}