<?php

namespace Test;

use Accolon\Izanami\Collection;
use PHPUnit\Framework\TestCase;
use Test\Models\User;

class CollectionTest extends TestCase
{
    public function testBasic()
    {
        $models = (new User)->all();
        $this->assertCount(2, $models);
    }

    public function testFilter()
    {
        $models = (new User)->all();
        $result = $models->filter(fn($model) => $model->id > 1);
        $this->assertCount(1, $result);
    }

    public function testFind()
    {
        $models = (new User)->all();
        $result = $models->find(fn($model) => $model->id == 1);
        $this->assertNotNull($result);
    }
}
