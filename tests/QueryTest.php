<?php

use Accolon\DataLayer\Db;
use PHPUnit\Framework\TestCase;
use Test\Test;

// require_once "./vendor/autoload.php";

class QueryTest extends TestCase
{
    public function testFind(): void
    {
        $db = new Test();
        $this->assertNotNull($db->find(1));
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
            ["id", "=", 1],
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