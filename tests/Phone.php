<?php

namespace Test;

use Accolon\Izanami\Model;

class Phone extends Model
{
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
