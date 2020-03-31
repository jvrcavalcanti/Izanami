<?php

use Accolon\DataLayer\Db;
use PHPUnit\Framework\TestCase;

require_once "./vendor/autoload.php";

class QueryTest extends TestCase
{
    public function testFind(): void
    {
        $db = Db::table('users');
        $this->assertNotNull($db->find(1));
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

    public function testBruteSQL(): void
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