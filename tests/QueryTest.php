<?php

namespace Accolon\DataLayer\Test;

use Accolon\DataLayer\DB;
use PHPUnit\Framework\TestCase;

require_once "./vendor/autoload.php";

class QueryTest extends TestCase
{
    public function testFind(): void
    {
        $db = DB::table('posts');
        $this->assertNotNull($db->find(1));
    }

    public function testBruteSQL(): void
    {
        $db = DB::bruteSql("SELECT * FROM posts WHERE id = 1");
        $this->assertIsArray($db);
    }

    public function testCount(): void
    {
        $db = DB::table("posts");
        $this->assertIsInt($db->count());
    }
}