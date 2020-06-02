<?php

use PHPUnit\Framework\TestCase;
use Accolon\DataLayer\Db;
use Accolon\DataLayer\Operation;

class UpdateTest extends TestCase
{
    public function testUpdate(): void
    {
        $db = Db::table('test');

        $result = $db->where(["username", "=", "Teste"])->update([
            "password" => "654321"
        ]);

        $this->assertTrue($result);
    }

    public function testUpdateTest()
    {
        $db = Db::table('test');

        $result = $db->where(["username", "=", "Teste"])->update([
            "password" => "123456"
        ]);

        $this->assertTrue($result);
    }
}