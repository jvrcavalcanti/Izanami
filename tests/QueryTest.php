<?php

use Accolon\DataLayer\DB;
use Accolon\DataLayer\Model;
use PHPUnit\Framework\TestCase;
use Test\Test;

// require_once "./vendor/autoload.php";

class QueryTest extends TestCase
{
    public function testFindById(): void
    {
        $db = new Test();
        $this->assertNotNull($db->findById(1));
    }

    public function testFindOrFail()
    {
        $db = new Test();

        try {
            $db->findOrFail("id", 5);
            $this->assertTrue(false);
        } catch (\Accolon\DataLayer\Exceptions\FailQueryException $e) {
            $this->assertTrue(true);
        }
    }

    public function testaddParams()
    {
        $db = new Test();

        $db->addParam("1");
        $db->addParams([2, 3]);

        $this->assertEquals(
            ["1", 2, 3],
            $db->getParams()
        );
    }

    public function testExists()
    {
        $db = new Test();

        $this->assertTrue(
            $db->where(["id", "=", 1])->exists()
        );
    }

    public function testFind()
    {
        $db = new Test();
        $result = $db->find("username", "Test");

        $this->assertNotNull($result);
        echo json_encode($result, JSON_PRETTY_PRINT);
    }

    public function testGetAll()
    {
        $db = new Test();
        
        $result = $db->getAll(["id, username"]);

        $this->assertIsArray($result);
    }

    public function testFirst()
    {
        $db = new Test();
        
        $result = $db->first(["id, username"]);

        $this->assertNotNull($result);
    }

    public function testFirstWhere()
    {
        $db = new Test();
        
        $result = $db->firstWhere("id", 1);

        $this->assertNotNull($result);
    }

    public function testMultipleWhere()
    {
        $db = DB::table('test');
        
        $result = $db->where([
            ["id", 1],
            ["username", "=", "Test"]
        ])->get();

        // var_dump(json_encode($result));

        $this->assertInstanceOf(Model::class, $result);
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
        $this->assertTrue(is_array($db->all()));
    }

    public function testQueryObject(): void
    {
        $db = DB::table('test');
        $this->assertTrue(is_object($db->get()));
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