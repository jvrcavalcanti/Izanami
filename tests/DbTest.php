<?php

require_once "./vendor/autoload.php";
require_once "./tests/Test.php";

use Accolon\DataLayer\Db;
use PHPUnit\Framework\TestCase;

class DbTest extends TestCase
{
    public function testConnection(): void
    {
        $this->assertIsObject(Db::connection());
    }

    public function testCreateObject(): void
    {
        $this->assertIsObject(Db::table('test'));
    }
}