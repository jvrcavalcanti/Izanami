<?php

require_once "./vendor/autoload.php";
require_once "./tests/Test.php";
require_once "./tests/User.php";
require_once "./tests/Phone.php";
require_once "./tests/Post.php";
require_once "./config.php";

use Accolon\Izanami\DB;
use PHPUnit\Framework\TestCase;
use Test\Test;

class DBTest extends TestCase
{
    public function testConnection(): void
    {
        $this->assertIsObject(DB::connection());
    }

    public function testCreateObject(): void
    {
        $this->assertIsObject(DB::table('test'));
    }
}