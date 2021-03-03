<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use Accolon\Izanami\DB;
use Test\Models\User;

class UpdateTest extends TestCase
{
    public function testUpdate(): void
    {
        $db = DB::table('users');

        $result = $db->where("username", "Teste")->update([
            "password" => "654321"
        ]);

        $this->assertTrue($result);
    }

    public function testUpdateTest()
    {
        $db = DB::table('users');

        $result = $db->where("username", "Teste")->update([
            "password" => "123456"
        ]);

        $this->assertTrue($result);
    }

    public function testUpdateWithSave()
    {
        $user = (new User)->find(1);

        $user->password = "654321";

        $result = $user->save();

        $this->assertTrue($result);
    }

    public function testUpdateWithSave2()
    {
        $db = new User();

        $user = $db->find(1);

        $user->password = "123456";

        $result = $user->save();

        $this->assertTrue($result);
    }
}
