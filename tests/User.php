<?php

namespace Test;

use Accolon\Izanami\Model;

class User extends Model
{
    public function phone()
    {
        return $this->hasOne(Phone::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
