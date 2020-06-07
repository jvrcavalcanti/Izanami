<?php

use Accolon\DataLayer\Db;
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

    public function testWhenTrue()
    {
        $db = new Test();

        $db->when(true, fn(Model $db) => $db->where("id", 1));

        $this->assertIsObject($db->get());
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

        $this->assertNotNull($db->find("username", "Test"));
    }

    public function testGetAll()
    {
        $db = new Test();
        
        $result = $db->select(["id, username"])->getAll();

        $this->assertIsArray($result);
    }

    public function testMultipleWhere()
    {
        $db = DB::table('test');
        
        $result = $db->where([
            ["id", 1],
            ["username", "=", "Test"]
        ])->get();

        // var_dump(json_encode($result));

        $this->assertIsObject($result);
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
        $this->assertTrue(is_object($db->get(true)));
    }

    public function testBruteSql(): void
    {
        $db = Db::bruteSql("SELECT * FROM test WHERE id = 1");
        $this->assertTrue($db);
    }

    public function testCount(): void
    {
        $db = DB::table('test');
        $this->assertIsInt($db->count());
    }
}