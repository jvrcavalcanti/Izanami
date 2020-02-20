<?php

namespace Accolon\DataLayer\Test;

require_once "./vendor/autoload.php";

use Accolon\DataLayer\Db;
use PHPUnit\Framework\TestCase;

class DbTest extends TestCase
{
    public function testConnection(): void
    {
        $this->assertIsObject(DB::connection());
    }

    public function testCreateObject(): void
    {
        $this->assertIsObject(DB::table('post'));
    }
}