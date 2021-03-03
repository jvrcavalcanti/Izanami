<?php

namespace Test;

use PHPUnit\Framework\TestCase;
use Test\Models\User;

class InsertTest extends TestCase
{
    public function testSave()
    {
        $db = new User();

        // var_dump($db->table);
        // exit;

        $db->username = "Test Create";
        $db->password = "123456";

        $result = $db->save();

        $this->assertTrue($result);

        if ($result) {
            $this->assertTrue($db->delete());
        }
    }

    public function testCreate()
    {
        $db = new User();

        $result = $db->create([
            "username" => "Test Create",
            "password" => "123456"
        ]);

        $this->assertTrue($result);

        if ($result) {
            $this->assertTrue($db->where("username", "Test Create")->delete());
        }
    }
}
