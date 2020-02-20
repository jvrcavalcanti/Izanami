<?php

namespace Accolon\DataLayer\Test;

use Accolon\DataLayer\Db;
use PHPUnit\Framework\TestCase;

require_once "./vendor/autoload.php";

class QueryTest extends TestCase
{
    public function testFind(): void
    {
        $db = Db::table('post');
        $this->assertNotNull($db->find(60));
    }

    public function testCount(): void
    {
        $db = Db::table("post");
        $this->assertIsInt($db->count());
    }
}