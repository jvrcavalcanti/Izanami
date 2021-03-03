<?php

namespace Test;

use Accolon\Izanami\DB;
use PHPUnit\Framework\TestCase;

class DBTest extends TestCase
{
    public function testConnection(): void
    {
        $this->assertIsObject(DB::connection());
    }

    public function testCreateObject(): void
    {
        $this->assertIsObject(DB::table('users'));
    }
}
