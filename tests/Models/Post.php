<?php

namespace Test\Models;

use Accolon\Izanami\Model;

class Post extends Model
{
    public function user()
    {
        return $this->belongsToOne(User::class);
    }

    public function tags()
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }
}
