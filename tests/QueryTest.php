<?php

use Accolon\DataLayer\Db;
use PHPUnit\Framework\TestCase;
use Test\User;

// require_once "./vendor/autoload.php";

class QueryTest extends TestCase
{
    public function testFind(): void
    {
        $db = new User();
        $this->assertNotNull($db->find(1));
    }

    public function testGetAll()
    {
        $db = new User();
        
        $result = $db->select(["id, username"])->getAll();

        $this->assertIsArray($result);
    }

    public function testMultipleWhere()
    {
        $db = DB::table('users');

        $result = $db->create([
            "username" => "TesteCreate",
            "password" => "123456"
        ]);
        
        $result = $db->where([
            ["password", "=", "123456"],
            ["username", "=", "TesteCreate"]
        ])->get();

        $this->assertIsObject($result);

        if ($result) {
            $db->where(["username", "=", "TestCreate"])->delete();
        }
    }

    public function testLimit()
    {
        $db = DB::table('users');

        $result = $db->create([
            "username" => "TesteCreate",
            "password" => "123456"
        ]);

        $result2 = $db->create([
            "username" => "TesteCreate2",
            "password" => "123456"
        ]);

        $cont = 2;

        $result = $db->limit($cont)->all();

        $this->assertEquals($cont, sizeof($result));

        if ($result || $result2 ) {
            $db->where(["password", "=", "123456"])->delete();
        }
    }

    public function testQueryAll(): void
    {
        $db = Db::table("users");
        $this->assertTrue(is_array($db->all()));
    }

    public function testQueryObject(): void
    {
        $db = Db::table("users");
        $this->assertTrue(is_object($db->get(true)));
    }

    public function testBruteSql(): void
    {
        $db = Db::bruteSql("SELECT * FROM users WHERE id = 1");
        $this->assertTrue($db);
    }

    public function testCount(): void
    {
        $db = Db::table("users");
        $this->assertIsInt($db->count());
    }
}