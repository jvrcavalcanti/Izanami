<?php

require_once "./vendor/autoload.php";
require_once "./tests/Test.php";

use Accolon\DataLayer\DB;
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