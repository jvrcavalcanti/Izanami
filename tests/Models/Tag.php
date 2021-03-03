<?php

namespace Test\Models;

use Accolon\Izanami\Model;

class Tag extends Model
{
    public function posts()
    {
        return $this->morphedByMany(Post::class, 'taggable');
    }
}
