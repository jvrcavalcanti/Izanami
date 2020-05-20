<?php

require_once "./vendor/autoload.php";
require_once "./tests/User.php";

use Accolon\DataLayer\Db;
use PHPUnit\Framework\TestCase;
use Test\User;

class DbTest extends TestCase
{
    public function testConnection(): void
    {
        $this->assertIsObject(Db::connection());
    }

    public function testCreateObject(): void
    {
        $this->assertIsObject(Db::table('users'));
    }

    public function testFactory()
    {
        $this->assertInstanceOf(User::class, User::build());
    }
}