<?php

namespace Test\Models;

use Accolon\Izanami\Model;

class User extends Model
{
    protected $sensitives = ["password"];

    public function phone()
    {
        return $this->hasOne(Phone::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
