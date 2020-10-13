<?php

use PHPUnit\Framework\TestCase;
use Test\Test;

class InsertTest extends TestCase
{
    public function testSave()
    {
        $db = new Test();

        // var_dump($db->table);
        // exit;

        $db->username = "Test Create";
        $db->password = "123456";

        $result = $db->save();

        $this->assertTrue($result);

        if ($result) {
            $db->delete();
        }
    }

    public function testCreate()
    {
        $db = new Test();

        $result = $db->create([
            "username" => "Test Create",
            "password" => "123456"
        ]);

        $this->assertTrue($result);

        if ($result) {
            $db->where("username", "=", "Test Create")->delete();
        }
    }
}