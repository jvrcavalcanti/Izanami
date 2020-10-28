<?php

namespace Test;

use Accolon\Izanami\Collection;
use Accolon\Izanami\DB;
use Accolon\Izanami\Model;
use PHPUnit\Framework\TestCase;
use Test\Test;

class QueryTest extends TestCase
{
    public function testHasOne()
    {
        $user = (new User())->find(1);

        $this->assertInstanceOf(Phone::class, $user->phone());
    }

    public function testBelongsToMany()
    {
        $phone = (new Phone())->find(1);

        $this->assertInstanceOf(User::class, $phone->users()[0]);
    }

    public function testBelongsToOne()
    {
        $post = (new Post)->find(1);

        $this->assertInstanceOf(User::class, $post->user());
    }

    public function testHasMany()
    {
        $user = (new User())->find(1);

        $this->assertInstanceOf(Post::class, $user->posts()[0]);
    }

    public function testMorphToMany()
    {
        $post = (new Post)->find(1);

        $this->assertInstanceOf(Tag::class, $post->tags()[0]);
    }

    public function testMorphedByMany()
    {
        $tag = (new Tag)->find(1);

        $this->assertInstanceOf(Post::class, $tag->posts()[0]);
    }

    public function testFindOrFail()
    {
        $db = new Test();

        try {
            $db->findOrFail(5);
            $this->assertTrue(false);
        } catch (\Accolon\Izanami\Exceptions\FailQueryException $e) {
            $this->assertTrue(true);
        }
    }

    public function testExists()
    {
        $db = new Test();

        $this->assertTrue(
            $db->where("id", 1)->exists()
        );
    }

    public function testFind()
    {
        $db = new Test();
        $result = $db->find(1);

        $this->assertNotNull($result);
    }

    public function testGetAll()
    {
        $db = new Test();
        
        $result = $db->asc()->getAll("id, username");

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function testFirst()
    {
        $db = new Test();
        
        $result = $db->first("id, username");

        $this->assertNotNull($result);
    }

    public function testFirstWhere()
    {
        $db = new Test();
        
        $result = $db->firstWhere("id", 1);

        $this->assertNotNull($result);
    }

    public function testMultipleWhere2()
    {
        $db = DB::table('test');

        $result = $db->where("id", 1)->where("username", "Test")->get();

        $this->assertInstanceOf(Model::class, $result);
    }

    public function testWhereOr()
    {
        $table = new Test();

        $result = $table->whereOr("id", 5)->whereOr("username", "Test")->get();

        $this->assertInstanceOf(Model::class, $result);
    }

    public function testWhereIn()
    {
        $table = new Test();

        $result = $table->whereIn("id", [1, 2])->getAll();

        $this->assertInstanceOf(Collection::class, $result);
    }

    public function testWhereNotIn()
    {
        $table = new Test();

        $result = $table->whereNotIn("id", [1, 2])->getAll();

        $this->assertCount(0, $result);
    }

    public function testWhen()
    {
        $table = new Test();

        $result = $table->when(true, function (Test $table) {
            $table->where('id', '>', 1);
        })->all();

        $this->assertCount(1, $result);
    }

    public function testLimit()
    {
        $db = DB::table('test');

        $cont = 2;

        $result = $db->limit($cont)->all();

        $this->assertEquals($cont, sizeof($result));
    }

    public function testQueryAll(): void
    {
        $db = DB::table('test');
        $this->assertTrue($db->all() instanceof Collection);
    }

    public function testQueryObject(): void
    {
        $db = DB::table('test');
        $this->assertNotNull($db->get());
    }

    public function testRaw(): void
    {
        $db = DB::raw("SELECT * FROM test WHERE id = 1");
        $this->assertTrue($db);
    }

    public function testCount(): void
    {
        $db = DB::table('test');
        $this->assertIsInt($db->count());
    }
}
