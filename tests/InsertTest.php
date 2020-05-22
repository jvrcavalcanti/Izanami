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
            "username" => "Test Create 2",
            "password" => "123456"
        ]);

        $this->assertTrue($result);

        if ($result) {
            $db->where(["username", "=", "Test Create 2"])->delete();
        }
    }

    public function testCreateMany()
    {
        $db = new User();

        $count = $db->count();

        $result = $db->createMany([
            [
                "username" => "Test 1",
                "password" => "1"
            ],
            [
                "username" => "Test 2",
                "password" => "2"
            ]
        ]);

        $this->assertEquals($count + 2, $db->count());

        if ($result) {
            $db->where([
                [
                    "username" => "Test 1",
                    "password" => "1"
                ],
                [
                    "username" => "Test 2",
                    "password" => "2"
                ]
            ])->delete();
        }
    }
}