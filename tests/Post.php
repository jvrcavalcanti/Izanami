<?php

namespace Test;

use Accolon\Izanami\Model;

class Post extends Model
{
    public function user()
    {
        return $this->belongsToOne(User::class);
    }
}