<?php

use PHPUnit\Framework\TestCase;
use Test\User;

class InsertTest extends TestCase
{
    public function testSave()
    {
        $db = new User();

        $db->username = "Test Create";
        $db->setPassword("123456");

        $result = $db->save();

        $this->assertTrue($result);

        if ($result) {
            $db->where(["username", "=", "Test Create"])->delete();
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
            $db->where(["username", "=", "Test Create"])->delete();
        }
    }
}