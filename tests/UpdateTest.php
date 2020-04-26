<?php

use PHPUnit\Framework\TestCase;
use Accolon\DataLayer\Db;
use Accolon\DataLayer\Operation;

class UpdateTest extends TestCase
{
    public function testUpdate(): void
    {
        $db = Db::table('users');

        $result = $db->where(["username", "=", "Teste"])->update([
            "email" => "teste@gmail.com"
        ]);

        $this->assertTrue($result);
    }

    public function testUpdateTest()
    {
        $db = Db::table('users');

        $result = $db->where(["username", "=", "Teste"])->update([
            "email" => "test@gmail.com"
        ]);

        $this->assertTrue($result);
    }
}