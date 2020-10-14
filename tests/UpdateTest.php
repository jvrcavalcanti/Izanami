<?php

use PHPUnit\Framework\TestCase;
use Accolon\Izanami\Db;
use Test\Test;

class UpdateTest extends TestCase
{
    public function testUpdate(): void
    {
        $db = Db::table('test');

        $result = $db->where("username", "Teste")->update([
            "password" => "654321"
        ]);

        $this->assertTrue($result);
    }

    public function testUpdateTest()
    {
        $db = Db::table('test');

        $result = $db->where("username", "Teste")->update([
            "password" => "123456"
        ]);

        $this->assertTrue($result);
    }

    public function testUpdateWithSave()
    {
        $user = (new Test)->find(1);

        $user->password = "654321";

        $result = $user->save();

        $this->assertTrue($result);
    }

    public function testUpdateWithSave2()
    {
        $db = new Test();

        $user = $db->find(1);

        $user->password = "123456";

        $result = $user->save();

        $this->assertTrue($result);
    }
}